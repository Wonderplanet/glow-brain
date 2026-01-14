using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Factories
{
    public class EnhanceQuestModelFactory : IEnhanceQuestModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }

        public EnhanceQuestModel CreateCurrentEnhanceQuestModel()
        {
            var userStageEnhance = GameRepository.GetGameFetch().UserStageEnhanceModels.FirstOrDefault();
            var mstStageId = userStageEnhance?.MstStageId ?? MasterDataId.Empty;
            
            MstQuestModel mstQuest = MstQuestModel.Empty;
            MstStageModel mstStage = MstStageModel.Empty;

            if (mstStageId.IsEmpty())
            {
                mstQuest = MstQuestDataRepository.GetMstQuestModels()
                    .Find(mst => mst.QuestType == QuestType.Enhance);
                mstStage = MstStageDataRepository.GetMstStagesFromMstQuestId(mstQuest.Id).First();
            }
            else
            {
                mstStage = MstStageDataRepository.GetMstStage(mstStageId);
                mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            }

            return new EnhanceQuestModel(mstQuest, mstStage);
        }
    }
}
