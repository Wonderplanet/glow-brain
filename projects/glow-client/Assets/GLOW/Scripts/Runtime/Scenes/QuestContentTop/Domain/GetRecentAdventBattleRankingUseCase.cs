using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.AdventBattleRanking.Domain.Models;
using Zenject;
namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class GetRecentAdventBattleRankingUseCase
    {
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IAdventBattleRankingModelFactory AdventBattleRankingModelFactory { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public async UniTask<AdventBattleRankingUseCaseModel> GetRecentAdventBattleRanking(CancellationToken cancellationToken)
        {
            var adventBattleModels = MstAdventBattleDataRepository.GetMstAdventBattleModels();

            // 過去、最新の中で最も終了日時が新しいものを取得(未来は含まれない)
            var recentAdventBattle = adventBattleModels
                .Where(m => m.StartDateTime <= TimeProvider.Now)
                .MaxBy(m => m.EndDateTime.Value);

            var mstAdventBattleId = recentAdventBattle?.Id ?? MasterDataId.Empty;
            if (mstAdventBattleId.IsEmpty())
            {
                return AdventBattleRankingUseCaseModel.Empty;
            }

            var currentRanking = await AdventBattleService.GetRanking(cancellationToken, mstAdventBattleId, false);

            var currentRankingModel = AdventBattleRankingModelFactory.CreateAdventBattleRankingElementUseCaseModel(
                mstAdventBattleId,
                currentRanking,
                true);

            return new AdventBattleRankingUseCaseModel(currentRankingModel);
        }
    }
}

