INSERT INTO `auth_rules` (`id`, `api`, `url`, `icon`, `title`, `pid`, `state`, `sort`) VALUES
(186, 'Distribution', '', '', '分销', 23, 1, 0),
(null, 'DistributionList', '', '', '分销列表', 186, 1, 0),
(null, 'CreateDistribution', '', '', '添加分销', 186, 0, 0),
(null, 'EditDistribution', '', '', '分销编辑', 186, 0, 0),
(null, 'DeleteDistribution', '', '', '分销删除', 186, 0, 0);

INSERT INTO `distributions` (`name`, `identification`, `level`, `state`) VALUES
('注册奖励现金', 'sys_registration_cash', 1, 0);
INSERT INTO `distribution_rules` (`distribution_id`, `name`, `type`, `level`, `price`) VALUES
(1, '1级分销', 0, 1,1000);