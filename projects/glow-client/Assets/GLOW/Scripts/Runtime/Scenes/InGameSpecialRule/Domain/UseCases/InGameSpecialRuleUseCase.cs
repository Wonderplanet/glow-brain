using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories;
using GLOW.Scenes.InGameSpecialRule.Domain.Models;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;
using Zenject;
namespace GLOW.Scenes.InGameSpecialRule.Domain.UseCases
{
    public class InGameSpecialRuleUseCase
    {
        [Inject] IInGameSpecialRuleModelFactory InGameSpecialRuleModelFactory { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public InGameSpecialRuleModel GetInGameSpecialRuleModel(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            var mstStage = MstStageModel.Empty;
            var mstQuest = MstQuestModel.Empty;
            if (specialRuleContentType == InGameContentType.Stage)
            {
                mstStage = MstStageDataRepository.GetMstStage(specialRuleTargetMstId);
                mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            }
            return InGameSpecialRuleModelFactory.Create(specialRuleContentType, specialRuleTargetMstId, mstQuest.QuestType);
        }
    }
}
