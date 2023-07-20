CREATE TABLE IF NOT EXISTS `#__youtubegallery` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `galleryname` varchar(50) NOT NULL,
  `gallerylist` text,
  `showtitle` tinyint(1) NOT NULL,
  `playvideo` tinyint(1) NOT NULL,
  `repeat` tinyint(1) NOT NULL,
  `fullscreen` tinyint(1) NOT NULL,
  `autoplay` tinyint(1) NOT NULL,
  `related` tinyint(1) NOT NULL,
  `showinfo` tinyint(1) NOT NULL,
  `bgcolor` varchar(20) NOT NULL,
  `cols` smallint(6) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `cssstyle` varchar(255) NOT NULL,
  `navbarstyle` varchar(255) NOT NULL,
  `thumbnailstyle` varchar(255) NOT NULL,
  `linestyle` varchar(255) NOT NULL,
  `showgalleryname` varchar(255) NOT NULL,
  `gallerynamestyle` varchar(255) NOT NULL,
  `showactivevideotitle` varchar(255) NOT NULL,
  `activevideotitlestyle` varchar(255) NOT NULL,
  `color1` varchar(255) NOT NULL,
  `color2` varchar(255) NOT NULL,
  `border` tinyint(1) NOT NULL,
  `description` tinyint(1) NOT NULL,
  `descr_position` smallint(6) NOT NULL,
  `descr_style` varchar(255) NOT NULL,


  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;