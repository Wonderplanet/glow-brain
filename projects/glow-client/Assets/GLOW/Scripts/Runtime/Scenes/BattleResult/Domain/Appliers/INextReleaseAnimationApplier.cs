using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Domain.Appliers
{
    public interface INextReleaseAnimationApplier
    {
        void UpdateReleaseAnimationRepository(
            MasterDataId selectedMstStageId, 
            IReadOnlyList<StageModel> stageModels,
            IReadOnlyList<UserStageEventModel> userStageEventModels);
    }
}
