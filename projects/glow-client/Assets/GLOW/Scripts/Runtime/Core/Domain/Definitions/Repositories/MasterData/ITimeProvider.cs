using System;

namespace GLOW.Core.Domain.Repositories
{
    public interface ITimeProvider
    {
        DateTimeOffset Now { get; }
    }
}
