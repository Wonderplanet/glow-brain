using System;

namespace GLOW.Core.Domain.Repositories
{
    public interface IContentTopAccessPreferenceRepository
    {
        bool HasValue { get; }
        DateTimeOffset GetLastAccessTime();
        void SaveAccessTime(DateTimeOffset accessDateTimeOffset);
    }
}
