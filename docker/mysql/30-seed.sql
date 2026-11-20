-- Minimal seed so the API can boot: bootstrapme.php does
-- PlatformRepository::getPlatformByKey(getenv('platform_key')) and fails without this row.
USE `idea-village-sandbox`;
INSERT INTO `kf_platforms` (`platform_key`, `uuid`, `status`, `created_at`, `updated_at`)
VALUES ('sandbox', UUID(), 'live', NOW(), NOW());
