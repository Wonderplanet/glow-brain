using System;
using GLOW.Core.Domain.Repositories;
using WonderPlanet.CultureSupporter.Time;

namespace GLOW.Core.Data.Repositories
{
    public class ServerTimeProvider : ITimeProvider
    {
        public DateTimeOffset Now => TimeProvider.DateTimeOffsetSource.Now;
    }
}
