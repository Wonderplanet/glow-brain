using System;
using GLOW.Core.Domain.Repositories;

namespace GLOW.Core.Data.Repositories
{
    public class LocalTimeProvider : ITimeProvider
    {
        public DateTimeOffset Now => DateTimeOffset.Now - TimeSpan.FromHours(9);
    }
}
