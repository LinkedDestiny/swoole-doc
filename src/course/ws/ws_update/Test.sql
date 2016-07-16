-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 14, 2016 at 10:27 PM
-- Server version: 5.5.46-0ubuntu0.14.04.2
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Test`
--
CREATE DATABASE IF NOT EXISTS `Test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `Test`;

-- --------------------------------------------------------

--
-- Table structure for table `table1`
--

DROP TABLE IF EXISTS `table1`;
CREATE TABLE `table1` (
  `field1` int(11) NOT NULL,
  `field2` int(11) NOT NULL,
  `field3` int(11) NOT NULL,
  `field4` int(11) NOT NULL,
  `field5` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table1`
--

INSERT INTO `table1` (`field1`, `field2`, `field3`, `field4`, `field5`) VALUES
(556, 10, 3, 4, 5),
(557, 10, 3, 4, 5),
(558, 10, 3, 4, 5),
(559, 10, 3, 4, 5),
(560, 10, 3, 4, 5),
(561, 10, 3, 4, 5),
(562, 10, 3, 4, 5),
(563, 10, 3, 4, 5),
(564, 10, 3, 4, 5),
(565, 10, 3, 4, 5),
(566, 10, 3, 4, 5),
(567, 10, 3, 4, 5),
(568, 10, 3, 4, 5),
(569, 10, 3, 4, 5),
(570, 10, 3, 4, 5),
(571, 10, 3, 4, 5),
(572, 10, 3, 4, 5),
(573, 10, 3, 4, 5),
(574, 10, 3, 4, 5),
(575, 10, 3, 4, 5),
(576, 10, 3, 4, 5),
(577, 10, 3, 4, 5),
(578, 10, 3, 4, 5),
(579, 10, 3, 4, 5),
(580, 10, 3, 4, 5),
(581, 10, 3, 4, 5),
(582, 10, 3, 4, 5),
(583, 10, 3, 4, 5),
(584, 10, 3, 4, 5),
(585, 10, 3, 4, 5),
(586, 10, 3, 4, 5),
(587, 10, 3, 4, 5),
(588, 10, 3, 4, 5),
(589, 10, 3, 4, 5),
(590, 10, 3, 4, 5),
(591, 10, 3, 4, 5),
(592, 10, 3, 4, 5),
(593, 10, 3, 4, 5),
(594, 10, 3, 4, 5),
(595, 10, 3, 4, 5),
(596, 10, 3, 4, 5),
(597, 10, 3, 4, 5),
(598, 10, 3, 4, 5),
(599, 10, 3, 4, 5),
(600, 10, 3, 4, 5),
(601, 10, 3, 4, 5),
(602, 10, 3, 4, 5),
(603, 10, 3, 4, 5),
(604, 10, 3, 4, 5),
(605, 10, 3, 4, 5),
(606, 10, 3, 4, 5),
(607, 10, 3, 4, 5),
(608, 10, 3, 4, 5),
(609, 10, 3, 4, 5),
(610, 10, 3, 4, 5),
(611, 10, 3, 4, 5),
(612, 10, 3, 4, 5),
(613, 10, 3, 4, 5),
(614, 10, 3, 4, 5),
(615, 10, 3, 4, 5),
(616, 10, 3, 4, 5),
(617, 10, 3, 4, 5),
(618, 10, 3, 4, 5),
(619, 10, 3, 4, 5),
(620, 10, 3, 4, 5),
(621, 10, 3, 4, 5),
(622, 10, 3, 4, 5),
(623, 10, 3, 4, 5),
(624, 10, 3, 4, 5),
(625, 10, 3, 4, 5),
(626, 10, 3, 4, 5),
(627, 10, 3, 4, 5),
(628, 10, 3, 4, 5),
(629, 10, 3, 4, 5),
(630, 10, 3, 4, 5),
(631, 10, 3, 4, 5),
(632, 10, 3, 4, 5),
(633, 10, 3, 4, 5),
(634, 10, 3, 4, 5),
(635, 10, 3, 4, 5),
(636, 10, 3, 4, 5),
(637, 10, 3, 4, 5),
(638, 10, 3, 4, 5),
(639, 10, 3, 4, 5),
(640, 10, 3, 4, 5),
(641, 10, 3, 4, 5),
(642, 10, 3, 4, 5),
(643, 10, 3, 4, 5),
(644, 10, 3, 4, 5),
(645, 10, 3, 4, 5),
(646, 10, 3, 4, 5),
(647, 10, 3, 4, 5),
(648, 10, 3, 4, 5),
(649, 10, 3, 4, 5),
(650, 10, 3, 4, 5),
(651, 10, 3, 4, 5),
(652, 10, 3, 4, 5),
(653, 10, 3, 4, 5),
(654, 10, 3, 4, 5),
(655, 10, 3, 4, 5),
(656, 10, 3, 4, 5),
(657, 10, 3, 4, 5),
(658, 10, 3, 4, 5),
(659, 10, 3, 4, 5),
(660, 10, 3, 4, 5),
(661, 10, 3, 4, 5),
(662, 10, 3, 4, 5),
(663, 10, 3, 4, 5),
(664, 10, 3, 4, 5),
(665, 10, 3, 4, 5),
(666, 10, 3, 4, 5),
(667, 10, 3, 4, 5),
(668, 10, 3, 4, 5),
(669, 10, 3, 4, 5),
(670, 10, 3, 4, 5),
(671, 10, 3, 4, 5),
(672, 10, 3, 4, 5),
(673, 10, 3, 4, 5),
(674, 10, 3, 4, 5),
(675, 10, 3, 4, 5),
(676, 10, 3, 4, 5),
(677, 10, 3, 4, 5),
(678, 10, 3, 4, 5),
(679, 10, 3, 4, 5),
(680, 10, 3, 4, 5),
(681, 10, 3, 4, 5),
(682, 10, 3, 4, 5),
(683, 10, 3, 4, 5),
(684, 10, 3, 4, 5),
(685, 10, 3, 4, 5),
(686, 10, 3, 4, 5),
(687, 10, 3, 4, 5),
(688, 10, 3, 4, 5),
(689, 10, 3, 4, 5),
(690, 10, 3, 4, 5),
(691, 10, 3, 4, 5),
(692, 10, 3, 4, 5),
(693, 10, 3, 4, 5),
(694, 10, 3, 4, 5),
(695, 10, 3, 4, 5),
(696, 10, 3, 4, 5),
(697, 10, 3, 4, 5),
(698, 10, 3, 4, 5),
(699, 10, 3, 4, 5),
(700, 10, 3, 4, 5),
(701, 10, 3, 4, 5),
(702, 10, 3, 4, 5),
(703, 10, 3, 4, 5),
(704, 10, 3, 4, 5),
(705, 10, 3, 4, 5),
(706, 10, 3, 4, 5),
(707, 10, 3, 4, 5),
(708, 10, 3, 4, 5),
(709, 10, 3, 4, 5),
(710, 10, 3, 4, 5),
(711, 10, 3, 4, 5),
(712, 10, 3, 4, 5),
(713, 10, 3, 4, 5),
(714, 10, 3, 4, 5),
(715, 10, 3, 4, 5),
(716, 10, 3, 4, 5),
(717, 10, 3, 4, 5),
(718, 10, 3, 4, 5),
(719, 10, 3, 4, 5),
(720, 10, 3, 4, 5),
(721, 10, 3, 4, 5),
(722, 10, 3, 4, 5),
(723, 10, 3, 4, 5),
(724, 10, 3, 4, 5),
(725, 10, 3, 4, 5),
(726, 10, 3, 4, 5),
(727, 10, 3, 4, 5),
(728, 10, 3, 4, 5),
(729, 10, 3, 4, 5),
(730, 10, 3, 4, 5),
(731, 10, 3, 4, 5),
(732, 10, 3, 4, 5),
(733, 10, 3, 4, 5),
(734, 10, 3, 4, 5),
(735, 10, 3, 4, 5),
(736, 10, 3, 4, 5),
(737, 10, 3, 4, 5),
(738, 10, 3, 4, 5),
(739, 10, 3, 4, 5),
(740, 10, 3, 4, 5),
(741, 10, 3, 4, 5),
(742, 10, 3, 4, 5),
(743, 10, 3, 4, 5),
(744, 10, 3, 4, 5),
(745, 10, 3, 4, 5),
(746, 10, 3, 4, 5),
(747, 10, 3, 4, 5),
(748, 10, 3, 4, 5),
(749, 10, 3, 4, 5),
(750, 10, 3, 4, 5),
(751, 10, 3, 4, 5),
(752, 10, 3, 4, 5),
(753, 10, 3, 4, 5),
(754, 10, 3, 4, 5),
(755, 10, 3, 4, 5),
(756, 10, 3, 4, 5),
(757, 10, 3, 4, 5),
(758, 10, 3, 4, 5),
(759, 10, 3, 4, 5),
(760, 10, 3, 4, 5),
(761, 10, 3, 4, 5),
(762, 10, 3, 4, 5),
(763, 10, 3, 4, 5),
(764, 10, 3, 4, 5);

--
-- Triggers `table1`
--
DROP TRIGGER IF EXISTS `add_delete`;
DELIMITER $$
CREATE TRIGGER `add_delete` AFTER DELETE ON `table1` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_insert`;
DELIMITER $$
CREATE TRIGGER `add_insert` AFTER INSERT ON `table1` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_update`;
DELIMITER $$
CREATE TRIGGER `add_update` AFTER UPDATE ON `table1` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `table2`
--

DROP TABLE IF EXISTS `table2`;
CREATE TABLE `table2` (
  `field1` int(11) NOT NULL,
  `field2` int(11) NOT NULL,
  `field3` int(11) NOT NULL,
  `field4` int(11) NOT NULL,
  `field5` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table2`
--

INSERT INTO `table2` (`field1`, `field2`, `field3`, `field4`, `field5`) VALUES
(10, 160, 42, 5, 6),
(30, 165, 43, 5, 6),
(50, 170, 44, 5, 6),
(200, 175, 45, 5, 6),
(12345, 50, 37, 4, 5);

--
-- Triggers `table2`
--
DROP TRIGGER IF EXISTS `add_delete2`;
DELIMITER $$
CREATE TRIGGER `add_delete2` AFTER DELETE ON `table2` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_insert2`;
DELIMITER $$
CREATE TRIGGER `add_insert2` AFTER INSERT ON `table2` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_update2`;
DELIMITER $$
CREATE TRIGGER `add_update2` AFTER UPDATE ON `table2` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `table3`
--

DROP TABLE IF EXISTS `table3`;
CREATE TABLE `table3` (
  `field1` int(11) NOT NULL,
  `field2` int(11) NOT NULL,
  `field3` int(11) NOT NULL,
  `field4` int(11) NOT NULL,
  `field5` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table3`
--

INSERT INTO `table3` (`field1`, `field2`, `field3`, `field4`, `field5`) VALUES
(20, 33, 412, 5, 6),
(40, 22, 321, 12345, 5),
(125, 33, 333, 6, 6);

--
-- Triggers `table3`
--
DROP TRIGGER IF EXISTS `add_delete3`;
DELIMITER $$
CREATE TRIGGER `add_delete3` AFTER DELETE ON `table3` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_insert3`;
DELIMITER $$
CREATE TRIGGER `add_insert3` AFTER INSERT ON `table3` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_update3`;
DELIMITER $$
CREATE TRIGGER `add_update3` AFTER UPDATE ON `table3` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `table4`
--

DROP TABLE IF EXISTS `table4`;
CREATE TABLE `table4` (
  `field1` int(11) NOT NULL,
  `field2` int(11) NOT NULL,
  `field3` int(11) NOT NULL,
  `field4` int(11) NOT NULL,
  `field5` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table4`
--

INSERT INTO `table4` (`field1`, `field2`, `field3`, `field4`, `field5`) VALUES
(20, 30, 4, 5, 6),
(30, 30, 41, 5, 6),
(40, 30, 46, 5, 6),
(120, 41, 43, 345, 123),
(12345, 223, 320, 4, 5);

--
-- Triggers `table4`
--
DROP TRIGGER IF EXISTS `add_delete4`;
DELIMITER $$
CREATE TRIGGER `add_delete4` AFTER DELETE ON `table4` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_insert4`;
DELIMITER $$
CREATE TRIGGER `add_insert4` AFTER INSERT ON `table4` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `add_update4`;
DELIMITER $$
CREATE TRIGGER `add_update4` AFTER UPDATE ON `table4` FOR EACH ROW begin
update tmp_record set is_update=1;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tmp_record`
--

DROP TABLE IF EXISTS `tmp_record`;
CREATE TABLE `tmp_record` (
  `is_update` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tmp_record`
--

INSERT INTO `tmp_record` (`is_update`) VALUES
(0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table1`
--
ALTER TABLE `table1`
  ADD PRIMARY KEY (`field1`);

--
-- Indexes for table `table2`
--
ALTER TABLE `table2`
  ADD PRIMARY KEY (`field1`);

--
-- Indexes for table `table3`
--
ALTER TABLE `table3`
  ADD PRIMARY KEY (`field1`);

--
-- Indexes for table `table4`
--
ALTER TABLE `table4`
  ADD PRIMARY KEY (`field1`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table1`
--
ALTER TABLE `table1`
  MODIFY `field1` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=765;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
