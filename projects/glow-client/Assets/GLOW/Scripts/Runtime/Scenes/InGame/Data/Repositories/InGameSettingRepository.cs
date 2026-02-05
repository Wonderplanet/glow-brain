using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Data.DataStores;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Data.Repositories
{
    public class InGameSettingRepository : IInGameSettingRepository
    {
        [Inject] IInGameSettingDataStore InGameSettingDataStore { get; }

        public InGameSettingModel GetInGameSetting()
        {
            var data = InGameSettingDataStore.GetInGameSetting();

            return new InGameSettingModel(
                new TickCount(data.SlipDamageInterval),
                new TickCount(data.PoisonDamageInterval),
                new TickCount(data.BurnDamageInterval),
                new TickCount(data.RegenerationInterval),
                new TickCount(data.SpecialAttackCoolTime));
        }
    }
}
