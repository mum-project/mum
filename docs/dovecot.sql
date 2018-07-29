##
## Dovecot
##

## password_query
SELECT
    mailboxes.local_part AS username,
    domains.domain,
    mailboxes.password
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%n'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1;

## user_query
SELECT
    mailboxes.homedir                                                   AS home,
    mailboxes.maildir                                                   AS mail,
    CONCAT('*:storage=', COALESCE(mailboxes.quota, domains.quota, 0), 'G') AS quota_rule
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%n'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1
    AND mailboxes.send_only = 0;

## iterate_query
SELECT
    mailboxes.local_part AS username,
    domains.domain
FROM mailboxes
    INNER JOIN domains ON mailboxes.domain_id = domains.id
WHERE
    mailboxes.local_part = '%n'
    AND domains.domain = '%d'
    AND domains.active = 1
    AND mailboxes.active = 1
    AND mailboxes.send_only = 0;
