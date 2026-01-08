using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpRewardGroupModel(
        MasterDataId Id,
        MasterDataId MstPvpId,
        IReadOnlyList<MstPvpRewardModel> Rewards,
        PvpRewardCategory RewardCategory,
        PvpRewardConditionValue ConditionValue)
    {
        public static MstPvpRewardGroupModel Empty { get; } = new MstPvpRewardGroupModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            new List<MstPvpRewardModel>(),
            PvpRewardCategory.RankClass,
            PvpRewardConditionValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}