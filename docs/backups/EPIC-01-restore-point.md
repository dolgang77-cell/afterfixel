# EPIC-01 Restore Point

- Created at: `2026-04-17 17:07:31 KST`
- Snapshot file: [nightlife-pre-epic01-20260417-170731.tar.gz](/var/www/nightlife/docs/backups/nightlife-pre-epic01-20260417-170731.tar.gz)
- Backup path: `/var/www/nightlife/docs/backups/nightlife-pre-epic01-20260417-170731.tar.gz`
- SHA-256: `1a5ff8e16eac600666fa04be0bcc78784d0b8881f0451d2723945924729264df`

## Restore

This restore overwrites the current `/var/www/nightlife` tree with the snapshot state.

```bash
tar -xzf /var/www/nightlife/docs/backups/nightlife-pre-epic01-20260417-170731.tar.gz -C /var/www
```

## Notes

- The snapshot was created before starting EPIC-01 work.
- The archive is a full project snapshot of `/var/www/nightlife`.
- If later changes also need separate rollback points, create a new timestamped snapshot before each epic.
