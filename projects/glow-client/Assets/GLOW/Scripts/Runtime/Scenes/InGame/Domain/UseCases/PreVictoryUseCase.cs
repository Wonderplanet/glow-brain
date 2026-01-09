using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class PreVictoryUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstMangaAnimationDataRepository MstMangaAnimationDataRepository { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }

        public PreVictoryUseCaseModel PreVictory()
        {
            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();
            // 降臨バトルとPvpは漫画アニメーション無い想定でEmptyを返す
            if (selectedStage.InGameType == InGameType.AdventBattle || selectedStage.InGameType == InGameType.Pvp)
            {
                return PreVictoryUseCaseModel.Empty;
            }

            MstStageModel mstStage = MstStageDataRepository.GetMstStage(selectedStage.SelectedStageId);

            var mangaAnimationModel = MstMangaAnimationDataRepository
                .GetMangaAnimationsByStageId(mstStage.Id)
                .FirstOrDefault(
                    model => model.ConditionType == MangaAnimationConditionType.Victory, 
                    MstMangaAnimationModel.Empty);

            return new PreVictoryUseCaseModel(mangaAnimationModel.AssetKey, mangaAnimationModel.AnimationSpeed);
        }
    }
}
