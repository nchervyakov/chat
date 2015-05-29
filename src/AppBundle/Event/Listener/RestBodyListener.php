<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 29.05.2015
 * Time: 13:02
  */



namespace AppBundle\Event\Listener;


use FOS\RestBundle\Decoder\DecoderProviderInterface;
use FOS\RestBundle\Normalizer\ArrayNormalizerInterface;
use FOS\RestBundle\Normalizer\Exception\NormalizationException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class RestBodyListener
{
    private $decoderProvider;
    private $throwExceptionOnUnsupportedContentType;
    private $defaultFormat;
    private $arrayNormalizer;
    private $normalizeForms;

    /**
     * Constructor.
     *
     * @param DecoderProviderInterface $decoderProvider
     * @param bool                     $throwExceptionOnUnsupportedContentType
     * @param ArrayNormalizerInterface $arrayNormalizer
     * @param bool                     $normalizeForms
     */
    public function __construct(
        DecoderProviderInterface $decoderProvider,
        $throwExceptionOnUnsupportedContentType = false,
        ArrayNormalizerInterface $arrayNormalizer = null,
        $normalizeForms = false
    ) {
        $this->decoderProvider = $decoderProvider;
        $this->throwExceptionOnUnsupportedContentType = $throwExceptionOnUnsupportedContentType;
        $this->arrayNormalizer = $arrayNormalizer;
        $this->normalizeForms = $normalizeForms;
    }

    /**
     * Sets the array normalizer.
     *
     * @param ArrayNormalizerInterface $arrayNormalizer
     *
     * @deprecated To be removed in FOSRestBundle 2.0.0 (constructor injection is used instead).
     */
    public function setArrayNormalizer(ArrayNormalizerInterface $arrayNormalizer)
    {
        $this->arrayNormalizer = $arrayNormalizer;
    }

    /**
     * Sets the fallback format if there's no Content-Type in the request.
     *
     * @param string $defaultFormat
     */
    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Core request handler.
     *
     * @param GetResponseEvent $event
     *
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $contentType = $request->headers->get('Content-Type');
        if ($contentType !== null) {
            $contentTypeParts = preg_split('/;\s+/', $contentType, -1, PREG_SPLIT_NO_EMPTY);
            $contentType = $contentTypeParts[0];
        }
        $isFormPostRequest = in_array($contentType, array('multipart/form-data', 'application/x-www-form-urlencoded'), true) && 'POST' === $method;
        $normalizeRequest = $this->normalizeForms && $isFormPostRequest;

        if (!$isFormPostRequest && in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'))) {
            $format = null === $contentType
                ? $request->getRequestFormat()
                : $request->getFormat($contentType);

            $format = $format ?: $this->defaultFormat;

            $content = $request->getContent();

            if (!$this->decoderProvider->supports($format)) {
                if (
                    $this->throwExceptionOnUnsupportedContentType &&
                    $this->isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
                ) {
                    throw new UnsupportedMediaTypeHttpException("Request body format '$format' not supported");
                }

                return;
            }

            if (!empty($content)) {
                $decoder = $this->decoderProvider->getDecoder($format);
                $data = $decoder->decode($content);
                if (is_array($data)) {
                    $request->request = new ParameterBag($data);
                    $normalizeRequest = true;
                } else {
                    throw new BadRequestHttpException('Invalid '.$format.' message received');
                }
            }
        }

        if (null !== $this->arrayNormalizer && $normalizeRequest) {
            $data = $request->request->all();

            try {
                $data = $this->arrayNormalizer->normalize($data);
            } catch (NormalizationException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            $request->request = new ParameterBag($data);
        }
    }

    private function isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
    {
        return false === ('DELETE' === $method && empty($content) && null === $contentType);
    }
}