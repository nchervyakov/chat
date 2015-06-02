<?php
/**
 * Created with IntelliJ IDEA by Nick Chervyakov.
 * User: Nikolay Chervyakov 
 * Date: 01.06.2015
 * Time: 11:42
  */



namespace AppBundle\Event\Listener;


use AppBundle\Entity\Emoticon;
use AppBundle\Entity\ImageMessage;
use AppBundle\Entity\Message;
use AppBundle\Entity\TextMessage;
use AppBundle\Entity\UserPhoto;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

class JMSSerializerSubscriber implements EventSubscriberInterface, ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Returns the events to which this class has subscribed.
     *
     * Return format:
     *     array(
     *         array('event' => 'the-event-name', 'method' => 'onEventName', 'class' => 'some-class', 'format' => 'json'),
     *         array(...),
     *     )
     *
     * The class may be omitted if the class wants to subscribe to events of all classes.
     * Same goes for the format key.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerializeUserPhoto', 'class' => 'AppBundle\\Entity\\UserPhoto'],
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerializeEmoticon', 'class' => 'AppBundle\\Entity\\Emoticon'],
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerializeMessage', 'class' => 'AppBundle\\Entity\\ImageMessage'],
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerializeMessage', 'class' => 'AppBundle\\Entity\\TextMessage'],
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerializeImageMessage', 'class' => 'AppBundle\\Entity\\ImageMessage'],
            ['event' => 'serializer.post_serialize', 'method' => 'onPostSerializeTextMessage', 'class' => 'AppBundle\\Entity\\TextMessage']
        ];
    }

    public function onPostSerializeUserPhoto(ObjectEvent $event)
    {
        /** @var UserPhoto $photo */
        $photo = $event->getObject();

        if ($photo->getFileName()) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();

            $url = $this->container->get('vich_uploader.templating.helper.uploader_helper')->asset($photo, 'file', 'AppBundle\\Entity\\UserPhoto');
            $request = $this->container->get('request_stack')->getMasterRequest();

            if ($request) {
                $url = $request->getSchemeAndHttpHost() . $url;
            }

            $visitor->addData('file_url', $url);
        }
    }

    public function onPostSerializeEmoticon(ObjectEvent $event) {
        /** @var Emoticon $emoticon */
        $emoticon = $event->getObject();

        if ($emoticon->getIcon()) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();

            $url = $this->container->get('vich_uploader.templating.helper.uploader_helper')->asset($emoticon, 'iconFile', 'AppBundle\\Entity\\Emoticon');
            $request = $this->container->get('request_stack')->getMasterRequest();

            if ($request) {
                $url = $request->getSchemeAndHttpHost() . $url;
            }

            $visitor->addData('icon_url', $url);
        }
    }

    public function onPostSerializeImageMessage(ObjectEvent $event)
    {
        /** @var ImageMessage $message */
        $message = $event->getObject();

        if ($message->getImage()) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();

            $url = $this->container->get('vich_uploader.templating.helper.uploader_helper')->asset($message, 'imageFile', 'AppBundle\\Entity\\ImageMessage');
            $thumbUrl = $this->container->get('vich_uploader.templating.helper.uploader_helper')->asset($message, 'imageFile', 'AppBundle\\Entity\\ImageMessage');
            $thumbUrl = $this->container->get('liip_imagine.templating.helper')->filter($thumbUrl, 'user_message_image_thumb');

            $request = $this->container->get('request_stack')->getMasterRequest();

            if ($request) {
                $url = $request->getSchemeAndHttpHost() . $url;
                //$thumbUrl = $request->getSchemeAndHttpHost() . $thumbUrl;
            }

            $visitor->addData('image_url', $url);
            $visitor->addData('thumb_url', $thumbUrl);
        }
    }

    public function onPostSerializeTextMessage(ObjectEvent $event)
    {
        /** @var TextMessage $message */
        $message = $event->getObject();

        if ($message->getContent()) {
            /** @var GenericSerializationVisitor $visitor */
            $visitor = $event->getVisitor();

            $visitor->addData('processed_content', $this->container->get('app.emoticon_manager')->convertEmoticons($message->getContent(), UrlGenerator::ABSOLUTE_URL));
        }
    }

    public function onPreSerializeMessage(PreSerializeEvent $event)
    {
        if ($event->getObject() instanceof Message) {
            /** @var Message $message */
            $message = $event->getObject();
            $metadata = $event->getContext()->getMetadataFactory()->getMetadataForClass(get_class($message));

            if (isset($metadata->propertyMetadata['discriminator'])) {
                /** @var StaticPropertyMetadata $propMeta */
                $propMeta = $metadata->propertyMetadata['discriminator'];
                $propMeta->groups = ['Default', 'message_list'];
            }
        }
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}