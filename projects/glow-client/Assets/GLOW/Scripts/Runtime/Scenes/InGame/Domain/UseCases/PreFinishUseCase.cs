using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class PreFinishUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstMangaAnimationDataRepository MstMangaAnimationDataRepository { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IPvpResultEvaluator PvpResultEvaluator { get; }

        public PreFinishUseCaseModel PreFinish()
        {
            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();
            // 降臨バトルとPvpは漫画アニメーション無い想定でEmptyを返す
            if (selectedStage.InGameType == InGameType.AdventBattle)
            {
                return PreFinishUseCaseModel.Empty;
            }

            if (selectedStage.InGameType == InGameType.Pvp)
            {
                var pvpResult = PvpResultEvaluator.Evaluate();
                return new PreFinishUseCaseModel(MangaAnimationAssetKey.Empty, MangaAnimationSpeed.Empty, pvpResult);
            }

            if (selectedStage.SelectedStageId.IsEmpty())
            {
                return PreFinishUseCaseModel.Empty;
            }

            MstStageModel mstStage = MstStageDataRepository.GetMstStage(selectedStage.SelectedStageId);
            var mangaAnimationModel = MstMangaAnimationDataRepository
                .GetMangaAnimationsByStageId(mstStage.Id)
                .FirstOrDefault(model => model.ConditionType == MangaAnimationConditionType.Finish,
                    MstMangaAnimationModel.Empty);

            return new PreFinishUseCaseModel(
                mangaAnimationModel.AssetKey,
                mangaAnimationModel.AnimationSpeed,
                PvpResultModel.Empty);
        }
    }
}
