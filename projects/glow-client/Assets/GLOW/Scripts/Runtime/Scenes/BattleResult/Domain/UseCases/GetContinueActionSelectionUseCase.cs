using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class GetContinueActionSelectionUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] IEnemyCountResultModelFactory EnemyCountResultModelFactory { get; }

        public ContinueActionSelectionModel GetModel()
        {
            var cost = MstConfigRepository.GetConfig(MstConfigKey.StageContinueDiamondAmount).Value.ToTotalDiamond();

            var continueAdMaxCount = MstConfigRepository.GetConfig(MstConfigKey.AdContinueMaxCount).Value.ToContinueCount();
            var currentContinueAdCount = GameRepository.GetGameFetchOther().UserInGameStatusModel.ContinueAdCount;
            var remainingContinueAdCount = continueAdMaxCount - currentContinueAdCount;

            var enemyCountResult = EnemyCountResultModelFactory.Create();

            return new ContinueActionSelectionModel(
                cost,
                remainingContinueAdCount,
                HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo(),
                enemyCountResult
            );
        }
    }
}
