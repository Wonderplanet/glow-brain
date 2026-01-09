using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.AdventBattleRanking.Domain.Models;
using Zenject;
namespace GLOW.Scenes.AdventBattleRanking.Domain.UseCases
{
    public class AdventBattleRankingUseCase
    {
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IAdventBattleRankingModelFactory AdventBattleRankingModelFactory { get; }

        public async UniTask<AdventBattleRankingUseCaseModel> GetAdventBattleRanking(CancellationToken cancellationToken, MasterDataId mstAdventBattleId)
        {
            var currentRanking = await AdventBattleService.GetRanking(cancellationToken, mstAdventBattleId, false);

            var currentRankingModel = AdventBattleRankingModelFactory.CreateAdventBattleRankingElementUseCaseModel(
                mstAdventBattleId,
                currentRanking,
                false);

            return new AdventBattleRankingUseCaseModel(currentRankingModel);
        }
    }
}
