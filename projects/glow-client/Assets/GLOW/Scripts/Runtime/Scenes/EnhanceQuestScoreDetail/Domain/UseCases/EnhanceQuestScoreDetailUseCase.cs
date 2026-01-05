using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.EnhanceQuestScoreDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Domain.UseCases
{
    public class EnhanceQuestScoreDetailUseCase
    {
        [Inject] IMstStageEnhanceRewardParamDataRepository MstStageEnhanceRewardParamDataRepository { get; }

        public EnhanceQuestScoreDetailModel GetScoreDetail()
        {
            var cells = MstStageEnhanceRewardParamDataRepository.GetStageEnhanceRewardParams()
                .OrderByDescending(mst => mst.MinThresholdScore)
                .Select(mst => new EnhanceQuestScoreDetailCellModel(
                    mst.MinThresholdScore,
                    mst.CoinRewardAmount,
                    mst.CoinRewardSizeType))
                .ToList();

            return new EnhanceQuestScoreDetailModel(cells);
        }
    }
}
