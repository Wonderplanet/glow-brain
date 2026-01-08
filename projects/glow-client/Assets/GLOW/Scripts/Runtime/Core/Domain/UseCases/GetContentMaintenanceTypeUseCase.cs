using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Domain.UseCases
{
    public class GetContentMaintenanceTypeUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public ContentMaintenanceType GetContentMaintenanceType(InGameContentType inGameContentType, MasterDataId mstId)
        {
            return inGameContentType switch
            {
                InGameContentType.AdventBattle => ContentMaintenanceType.AdventBattle,
                InGameContentType.Pvp => ContentMaintenanceType.Pvp,
                InGameContentType.Stage => GetStageContentMaintenanceType(mstId),
                _ => ContentMaintenanceType.Non
            };
        }

        ContentMaintenanceType GetStageContentMaintenanceType(MasterDataId mstId)
        {
            var mstStage = MstStageDataRepository.GetMstStage(mstId);
            if (mstStage == null) return ContentMaintenanceType.Non;

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            if (mstQuest == null) return ContentMaintenanceType.Non;

            return mstQuest.QuestType == QuestType.Enhance
                ? ContentMaintenanceType.EnhanceQuest
                : ContentMaintenanceType.Non;
        }
    }
}
