using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.Models;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class GetContinueDiamondUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IEnemyCountResultModelFactory EnemyCountResultModelFactory { get; }

        public ContinueDiamondModel GetModel()
        {
            var userParameterModel = GameRepository.GetGameFetch().UserParameterModel;
            var paidDiamond = userParameterModel.GetPaidDiamondFromPlatform(SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var freeDiamond = userParameterModel.FreeDiamond;

            var cost = MstConfigRepository.GetConfig(MstConfigKey.StageContinueDiamondAmount).Value.ToTotalDiamond();
            var afterDiamond = DiamondCalculator.CalculateAfterDiamonds(paidDiamond, freeDiamond, cost);
            bool isLackOfDiamond = cost > paidDiamond + freeDiamond;

            var enemyCountResult = EnemyCountResultModelFactory.Create();

            return new ContinueDiamondModel(
                cost,
                paidDiamond,
                freeDiamond,
                afterDiamond.paid,
                afterDiamond.free,
                isLackOfDiamond,
                enemyCountResult
            );
        }
    }
}
