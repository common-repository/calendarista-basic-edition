CREATE TABLE prefix_calendarista_availability (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  availableDate DATETIME NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  customChargeDays INT(11) NULL DEFAULT NULL,
  customCharge DECIMAL(19,4) NULL DEFAULT NULL,
  customChargeMode TINYINT(4) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  depositMode TINYINT(4) NULL DEFAULT NULL,
  returnCost DECIMAL(19,4) NULL DEFAULT NULL,
  returnOptional TINYINT(4) NULL DEFAULT NULL,
  seats INT(11) NULL DEFAULT NULL,
  daysInPackage INT(11) NULL DEFAULT NULL,
  selectableSeats TINYINT(4) NULL DEFAULT NULL,
  fullDay TINYINT(4) NULL DEFAULT NULL,
  hasRepeat TINYINT(4) NULL DEFAULT NULL,
  repeatFrequency TINYINT(4) NULL DEFAULT NULL,
  repeatInterval TINYINT(4) NULL DEFAULT NULL,
  repeatWeekdayList VARCHAR(45) NULL DEFAULT NULL,
  checkinWeekdayList VARCHAR(45) NULL DEFAULT NULL,
  checkoutWeekdayList VARCHAR(45) NULL DEFAULT NULL,
  terminateMode TINYINT(4) NULL DEFAULT NULL,
  terminateAfterOccurrence TINYINT(4) NULL DEFAULT NULL,
  endDate DATETIME NULL DEFAULT NULL,
  color VARCHAR(45) NULL DEFAULT NULL,
  timezone VARCHAR(256) NULL DEFAULT NULL,
  imageUrl VARCHAR(256) NULL DEFAULT NULL,
  regionLat VARCHAR(256) NULL DEFAULT NULL,
  regionLng VARCHAR(256) NULL DEFAULT NULL,
  regionAddress VARCHAR(256) NULL DEFAULT NULL,
  regionMarkerIconUrl VARCHAR(256) NULL DEFAULT NULL,
  regionMarkerIconWidth INT(11) NULL DEFAULT NULL,
  regionMarkerIconHeight INT(11) NULL DEFAULT NULL,
  regionInfoWindowIcon VARCHAR(256) NULL DEFAULT NULL,
  regionInfoWindowDescription BLOB NULL DEFAULT NULL,
  styledMaps BLOB NULL DEFAULT NULL,
  showMapMarker TINYINT(4) NULL DEFAULT NULL,
  checkinWeekdays VARCHAR(45) NULL DEFAULT NULL,
  checkoutWeekdays VARCHAR(45) NULL DEFAULT NULL,
  minimumNotice INT(11) NULL DEFAULT NULL,
  maximumNotice INT(11) NULL DEFAULT NULL,
  bookingDaysMinimum INT(11) NULL DEFAULT NULL,
  bookingDaysMaximum INT(11) NULL DEFAULT NULL,
  maxTimeslots INT(11) NULL DEFAULT NULL,
  minimumTimeslotCharge DECIMAL(19,4) NULL DEFAULT NULL,
  turnoverBefore INT(11) NULL DEFAULT NULL,
  turnoverAfter INT(11) NULL DEFAULT NULL,
  syncList VARCHAR(512) NULL DEFAULT NULL,
  description BLOB NULL DEFAULT NULL,
  seatsMinimum INT(11) NULL DEFAULT NULL,
  timeMode TINYINT(4) NULL DEFAULT NULL,
  displayRemainingSeats TINYINT(4) NULL DEFAULT NULL,
  searchThumbnailUrl VARCHAR(256) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  seatsMaximum INT(11) NULL DEFAULT NULL,
  displayRemainingSeatsMessage TINYINT(4) NULL DEFAULT NULL,
  timeDisplayMode TINYINT(4) NULL DEFAULT NULL,
  dayCountMode TINYINT(4) NULL DEFAULT NULL,
  appendPackagePeriodToName TINYINT(4) NULL DEFAULT NULL,
  minimumNoticeMinutes INT(11) NULL DEFAULT NULL,
  turnoverBeforeMin INT(11) NULL DEFAULT NULL,
  turnoverAfterMin INT(11) NULL DEFAULT NULL,
  extendTimeRangeNextDay TINYINT(4) NULL DEFAULT NULL,
  minTime INT(11) NULL DEFAULT NULL,
  maxTime INT(11) NULL DEFAULT NULL,
  maxDailyRepeatFrequency TINYINT(4) NULL DEFAULT NULL,
  maxWeeklyRepeatFrequency TINYINT(4) NULL DEFAULT NULL,
  maxMonthlyRepeatFrequency TINYINT(4) NULL DEFAULT NULL,
  maxYearlyRepeatFrequency TINYINT(4) NULL DEFAULT NULL,
  maxRepeatOccurrence INT(11) NULL DEFAULT NULL,
  returnSameDay TINYINT(4) NULL DEFAULT NULL,
  maxRepeatFrequency INT(11) NULL DEFAULT NULL,
  guestNameRequired TINYINT(4) NULL DEFAULT NULL,
  displayDateSelectionReq TINYINT(4) NULL DEFAULT NULL,
  enableFullAmountOrDeposit TINYINT(4) NULL DEFAULT NULL,
  fullAmountDiscount DECIMAL(19,4) NULL DEFAULT NULL,
  instructions BLOB NULL DEFAULT NULL,
  hideMapDisplay TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_availability_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  status TINYINT(4) NULL DEFAULT NULL,
  fromDate DATETIME NULL DEFAULT NULL,
  toDate DATETIME NULL DEFAULT NULL,
  startTimeId BIGINT(20) NULL DEFAULT NULL,
  endTimeId BIGINT(20) NULL DEFAULT NULL,
  fullDay TINYINT(4) NULL DEFAULT NULL,
  timezone VARCHAR(256) NULL DEFAULT NULL,
  serverTimezone VARCHAR(256) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  returnCost DECIMAL(19,4) NULL DEFAULT NULL,
  seats INT(11) NULL DEFAULT NULL,
  color VARCHAR(45) NULL DEFAULT NULL,
  calendarMode TINYINT(4) NULL DEFAULT NULL,
  userEmail VARCHAR(256) NULL DEFAULT NULL,
  regionLat VARCHAR(256) NULL DEFAULT NULL,
  regionLng VARCHAR(256) NULL DEFAULT NULL,
  regionAddress VARCHAR(256) NULL DEFAULT NULL,
  synchedBookingId VARCHAR(256) NULL DEFAULT NULL,
  synchedBookingDescription BLOB NULL DEFAULT NULL,
  synchedBookingSummary BLOB NULL DEFAULT NULL,
  synchedBookingLocation VARCHAR(256) NULL DEFAULT NULL,
  synchedMode TINYINT(4) NULL DEFAULT NULL,
  gcalId BIGINT(20) NULL DEFAULT NULL,
  calendarId VARCHAR(256) NULL DEFAULT NULL,
  repeated TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_coupons (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId INT(11) NULL DEFAULT '-1',
  projectName VARCHAR(256) NULL DEFAULT NULL,
  code CHAR(40) NULL DEFAULT NULL,
  couponType TINYINT(4) NULL DEFAULT NULL,
  discount DECIMAL(19,4) NULL DEFAULT NULL,
  orderMinimum DECIMAL(19,4) NULL DEFAULT NULL,
  expirationDate DATETIME NULL DEFAULT NULL,
  emailedTo VARCHAR(256) NULL DEFAULT NULL,
  discountMode TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_error_log (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  entryDate DATETIME NULL DEFAULT NULL,
  message BLOB NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_feeds (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  feedUrl VARCHAR(256) NULL DEFAULT NULL,
  dateCreated DATETIME NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_formelement (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  label VARCHAR(256) NULL DEFAULT NULL,
  elementType INT(11) NULL DEFAULT NULL,
  lineSeparator TINYINT(4) NULL DEFAULT NULL,
  className VARCHAR(50) NULL DEFAULT NULL,
  options BLOB NULL DEFAULT NULL,
  defaultOptionItem VARCHAR(256) NULL DEFAULT NULL,
  defaultSelectedOptionItem VARCHAR(256) NULL DEFAULT NULL,
  validation BLOB NULL DEFAULT NULL,
  content BLOB NULL DEFAULT NULL,
  placeHolder VARCHAR(256) NULL DEFAULT NULL,
  country VARCHAR(3) NULL DEFAULT NULL,
  phoneNumberField TINYINT(4) NULL DEFAULT NULL,
  guestField TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_formelement_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  elementId INT(11) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  label VARCHAR(256) NULL DEFAULT NULL,
  value VARCHAR(256) NULL DEFAULT NULL,
  guestIndex INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_holidays (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  holiday DATETIME NULL DEFAULT NULL,
  timeslotId BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_map (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  regionAddress VARCHAR(256) NULL DEFAULT NULL,
  regionLat VARCHAR(256) NULL DEFAULT NULL,
  regionLng VARCHAR(256) NULL DEFAULT NULL,
  fromPlacesPreload TINYINT(4) NULL DEFAULT NULL,
  toPlacesPreload TINYINT(4) NULL DEFAULT NULL,
  waypointMarkerIconUrl VARCHAR(256) NULL DEFAULT NULL,
  waypointMarkerIconWidth INT(11) NULL DEFAULT NULL,
  waypointMarkerIconHeight INT(11) NULL DEFAULT NULL,
  optimizeWayPoints TINYINT(4) NULL DEFAULT NULL,
  unitType TINYINT(4) NULL DEFAULT NULL,
  queryLimitTimeout INT(11) NULL DEFAULT NULL,
  styledMaps BLOB NULL DEFAULT NULL,
  highway TINYINT(4) NULL DEFAULT NULL,
  toll TINYINT(4) NULL DEFAULT NULL,
  traffic TINYINT(4) NULL DEFAULT NULL,
  zoom INT(11) NULL DEFAULT NULL,
  panToZoom INT(11) NULL DEFAULT NULL,
  mapHeight INT(11) NULL DEFAULT NULL,
  enableDirection TINYINT(4) NULL DEFAULT NULL,
  enableDirectionButton TINYINT(4) NULL DEFAULT NULL,
  enableDistance TINYINT(4) NULL DEFAULT NULL,
  enableDistanceInfo TINYINT(4) NULL DEFAULT NULL,
  enableHighway TINYINT(4) NULL DEFAULT NULL,
  enableTolls TINYINT(4) NULL DEFAULT NULL,
  enableTraffic TINYINT(4) NULL DEFAULT NULL,
  enableWaypointButton TINYINT(4) NULL DEFAULT NULL,
  enableDepartureField TINYINT(4) NULL DEFAULT NULL,
  enableDestinationField TINYINT(4) NULL DEFAULT NULL,
  enableFindMyPosition TINYINT(4) NULL DEFAULT NULL,
  enableScrollWheel TINYINT(4) NULL DEFAULT NULL,
  enableContextMenu TINYINT(4) NULL DEFAULT NULL,
  draggableMarker TINYINT(4) NULL DEFAULT NULL,
  showDirectionStepsInline TINYINT(4) NULL DEFAULT NULL,
  showInfoWindow TINYINT(4) NULL DEFAULT NULL,
  driving TINYINT(4) NULL DEFAULT NULL,
  labelDriving VARCHAR(45) NULL DEFAULT NULL,
  walking TINYINT(4) NULL DEFAULT NULL,
  labelWalking VARCHAR(45) NULL DEFAULT NULL,
  bicycling TINYINT(4) NULL DEFAULT NULL,
  labelBicycling VARCHAR(45) NULL DEFAULT NULL,
  transit TINYINT(4) NULL DEFAULT NULL,
  labelTransit VARCHAR(45) NULL DEFAULT NULL,
  defaultTravelMode VARCHAR(45) NULL DEFAULT NULL,
  minimumUnitValue INT(11) NULL DEFAULT NULL,
  minimumUnitCost DECIMAL(19,4) NULL DEFAULT NULL,
  departureContextMenuLabel VARCHAR(256) NULL DEFAULT NULL,
  destinationContextMenuLabel VARCHAR(256) NULL DEFAULT NULL,
  waypointContextMenuLabel VARCHAR(256) NULL DEFAULT NULL,
  costMode TINYINT(4) NULL DEFAULT NULL,
  unitCost DECIMAL(19,4) NULL DEFAULT NULL,
  displayMap TINYINT(4) NULL DEFAULT NULL,
  restrictLat VARCHAR(256) NULL DEFAULT NULL,
  restrictLng VARCHAR(256) NULL DEFAULT NULL,
  restrictRadius INT(11) NULL DEFAULT NULL,
  restrictAddress VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_map_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  fromAddress VARCHAR(256) NULL DEFAULT NULL,
  fromLat VARCHAR(256) NULL DEFAULT NULL,
  fromLng VARCHAR(256) NULL DEFAULT NULL,
  toAddress VARCHAR(256) NULL DEFAULT NULL,
  toLat VARCHAR(256) NULL DEFAULT NULL,
  toLng VARCHAR(256) NULL DEFAULT NULL,
  unitType TINYINT(4) NULL DEFAULT NULL,
  distance DECIMAL(19,4) NULL DEFAULT NULL,
  duration DECIMAL(19,4) NULL DEFAULT NULL,
  fromPlaceId BIGINT(20) NULL DEFAULT NULL,
  toPlaceId BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_optional (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  groupId INT(11) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  quantity INT(11) NULL DEFAULT NULL,
  doubleCostIfReturn TINYINT(4) NULL DEFAULT NULL,
  description BLOB NULL DEFAULT NULL,
  thumbnailUrl VARCHAR(256) NULL DEFAULT NULL,
  minIncrement INT(11) NULL DEFAULT NULL,
  maxIncrement INT(11) NULL DEFAULT NULL,
  limitMode TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_optional_group (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NOT NULL,
  orderIndex INT(11) NOT NULL,
  name VARCHAR(256) NOT NULL,
  displayMode TINYINT(4) NOT NULL,
  minRequired INT(11) NULL DEFAULT NULL,
  multiply TINYINT(4) NULL DEFAULT NULL,
  maxSelection INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_optionals_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  optionalId BIGINT(20) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  groupName VARCHAR(256) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  groupOrderIndex INT(11) NULL DEFAULT NULL,
  groupId BIGINT(20) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  incrementValue INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_order (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  invoiceId VARCHAR(45) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  stagingId VARCHAR(45) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  userId BIGINT(20) NULL DEFAULT NULL,
  fullName VARCHAR(256) NULL DEFAULT NULL,
  email VARCHAR(256) NULL DEFAULT NULL,
  orderDate DATETIME NULL DEFAULT NULL,
  paymentStatus TINYINT(4) NULL DEFAULT NULL,
  transactionId VARCHAR(256) NULL DEFAULT NULL,
  totalAmount DECIMAL(19,4) NULL DEFAULT NULL,
  currency VARCHAR(3) NULL DEFAULT NULL,
  currencySymbol VARCHAR(45) NULL DEFAULT NULL,
  discount DECIMAL(19,4) NULL DEFAULT NULL,
  discountMode TINYINT(4) NULL DEFAULT NULL,
  tax DECIMAL(19,4) NULL DEFAULT NULL,
  refundAmount DECIMAL(19,4) NULL DEFAULT NULL,
  paymentDate DATETIME NULL DEFAULT NULL,
  paymentsMode TINYINT(4) NULL DEFAULT NULL,
  timezone VARCHAR(256) NULL DEFAULT NULL,
  serverTimezone VARCHAR(256) NULL DEFAULT NULL,
  paymentOperator VARCHAR(256) NULL DEFAULT NULL,
  deposit DECIMAL(19,4) NULL DEFAULT NULL,
  depositMode TINYINT(4) NULL DEFAULT NULL,
  balance DECIMAL(19,4) NULL DEFAULT NULL,
  secretKey VARCHAR(256) NULL DEFAULT NULL,
  wooCommerceOrderId BIGINT(20) NULL DEFAULT NULL,
  requestId VARCHAR(256) NULL DEFAULT NULL,
  taxMode TINYINT(4) NULL DEFAULT NULL,
  repeatWeekdayList VARCHAR(45) NULL DEFAULT NULL,
  repeatFrequency INT(11) NULL DEFAULT NULL,
  repeatInterval INT(11) NULL DEFAULT NULL,
  terminateAfterOccurrence INT(11) NULL DEFAULT NULL,
  couponCode VARCHAR(40) NULL DEFAULT NULL,
  upfrontPayment TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_place (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  mapId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  orderIndex INT(11) NULL DEFAULT NULL,
  placeType TINYINT(4) NULL DEFAULT NULL,
  lat VARCHAR(256) NULL DEFAULT NULL,
  lng VARCHAR(256) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  markerIcon VARCHAR(256) NULL DEFAULT NULL,
  markerIconWidth INT(11) NULL DEFAULT NULL,
  markerIconHeight INT(11) NULL DEFAULT NULL,
  infoWindowIcon VARCHAR(256) NULL DEFAULT NULL,
  infoWindowDescription BLOB NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_place_aggregate_cost (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  mapId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  departurePlaceId BIGINT(20) NULL DEFAULT NULL,
  destinationPlaceId BIGINT(20) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  exclude TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_project (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderIndex INT(11) NULL DEFAULT NULL,
  status TINYINT(4) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  description BLOB NULL DEFAULT NULL,
  previewUrl VARCHAR(256) NOT NULL,
  calendarMode TINYINT(4) NULL DEFAULT NULL,
  membershipRequired TINYINT(4) NULL DEFAULT NULL,
  enableStrongPassword TINYINT(4) NULL DEFAULT NULL,
  paymentsMode TINYINT(4) NULL DEFAULT NULL,
  enableCoupons TINYINT(4) NULL DEFAULT NULL,
  reminder INT(11) NULL DEFAULT NULL,
  wooProductId BIGINT(20) NULL DEFAULT NULL,
  previewImageHeight INT(11) NULL DEFAULT NULL,
  searchPage INT(11) NULL DEFAULT NULL,
  optionalByService TINYINT(4) NULL DEFAULT NULL,
  repeatPageSize INT(11) NULL DEFAULT NULL,
  thankyouReminder INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_reminders (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  orderId INT(11) NULL DEFAULT NULL,
  fullName VARCHAR(256) NULL DEFAULT NULL,
  email VARCHAR(256) NULL DEFAULT NULL,
  sentDate DATETIME NULL DEFAULT NULL,
  bookedAvailabilityId BIGINT(20) NULL DEFAULT NULL,
  reminderType TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_roles (
  id INT(11) NOT NULL AUTO_INCREMENT,
  userId BIGINT(20) NULL DEFAULT NULL,
  email VARCHAR(256) NULL DEFAULT NULL,
  projectId INT(11) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_staff (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  userId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  name VARCHAR(256) NULL DEFAULT NULL,
  email VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX id_UNIQUE (id ASC)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_staging (
  id VARCHAR(45) NOT NULL,
  viewState BLOB NULL DEFAULT NULL,
  entryDate DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_string_resources (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_style (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_timeslot (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  weekday TINYINT(4) NULL DEFAULT NULL,
  timeslot VARCHAR(45) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  day DATETIME NULL DEFAULT NULL,
  seats INT(11) NULL DEFAULT NULL,
  bookedSeats INT(11) NULL DEFAULT NULL,
  paddingTimeBefore INT(11) NULL DEFAULT NULL,
  paddingTimeAfter INT(11) NULL DEFAULT NULL,
  seatsMinimum INT(11) NULL DEFAULT NULL,
  seatsMaximum INT(11) NULL DEFAULT NULL,
  deal TINYINT(4) NULL DEFAULT NULL,
  startTime TINYINT(4) NULL DEFAULT NULL,
  returnTrip TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_waypoint (
  id INT(11) NOT NULL AUTO_INCREMENT,
  mapId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  address VARCHAR(256) NULL DEFAULT NULL,
  lat VARCHAR(256) NULL DEFAULT NULL,
  lng VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;

CREATE TABLE prefix_calendarista_waypoint_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  address VARCHAR(256) NULL DEFAULT NULL,
  lat VARCHAR(256) NULL DEFAULT NULL,
  lng VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
CREATE TABLE prefix_calendarista_season (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  seasonId BIGINT(20) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  start DATETIME NULL DEFAULT NULL,
  end DATETIME NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  percentageBased TINYINT(4) NULL DEFAULT NULL,
  costMode TINYINT(4) NULL DEFAULT NULL,
  repeatWeekdayList VARCHAR(45) NULL DEFAULT NULL,
  bookingDaysMinimum INT(11) NULL DEFAULT NULL,
  bookingDaysMaximum INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_gdpr (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  requestDate DATETIME NULL DEFAULT NULL,
  userEmail VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_auth (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  password VARCHAR(256) NULL DEFAULT NULL,
  userEmail VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_pricing_scheme (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  seasonId BIGINT(20) NULL DEFAULT NULL,
  days INT(11) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_dynamic_field (
  id INT(11) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  label VARCHAR(256) NULL DEFAULT NULL,
  data BLOB NULL DEFAULT NULL,
  limitBySeat TINYINT(4) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  required TINYINT(4) NULL DEFAULT NULL,
  fixedCost TINYINT(4) NULL DEFAULT NULL,
  byOptional TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_dynamic_field_booked (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  dynamicFieldId INT(11) NULL DEFAULT NULL,
  label VARCHAR(256) NULL DEFAULT NULL,
  value INT(11) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  limitBySeat TINYINT(4) NULL DEFAULT NULL,
  byOptional TINYINT(4) NULL DEFAULT NULL,
  fixedCost TINYINT(4) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_dynamic_field_pricing (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  dynamicFieldId INT(11) NULL DEFAULT NULL,
  cost DECIMAL(19,4) NULL DEFAULT NULL,
  fieldValue INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_gcal (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  gcalProfileId INT(11) NULL DEFAULT NULL,
  calendarId VARCHAR(256) NULL DEFAULT NULL,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  projectName VARCHAR(256) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  calendarName VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_gcal_profile (
  id INT(11) NOT NULL AUTO_INCREMENT,
  userId BIGINT(20) NULL DEFAULT NULL,
  clientId VARCHAR(256) NULL DEFAULT NULL,
  clientSecret VARCHAR(256) NULL DEFAULT NULL,
  accessToken BLOB NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_tags (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NULL DEFAULT NULL,
  orderIndex BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_tags_availability (
  id INT(11) NOT NULL AUTO_INCREMENT,
  tagId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_availability_day (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  projectId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  individualDay DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;
  
  CREATE TABLE prefix_calendarista_order_availability (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  orderId BIGINT(20) NULL DEFAULT NULL,
  availabilityId BIGINT(20) NULL DEFAULT NULL,
  availabilityName VARCHAR(256) NULL DEFAULT NULL,
  PRIMARY KEY (id)
  ) charset_collate_placeholder;