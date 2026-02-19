using System;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Shop;
using Zenject;

namespace GLOW.Scenes.PackShop.Domain.UseCase
{
    public class GetRemainCountdownTimeUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }

        public TimeSpan GetRemainCountDown(EndDateTime endTime)
        {
            return endTime - TimeProvider.Now;
        }
    }
}
