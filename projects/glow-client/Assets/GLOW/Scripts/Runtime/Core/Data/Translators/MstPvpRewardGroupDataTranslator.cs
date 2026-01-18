using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Data.Translators
{
    public class MstPvpRewardGroupDataTranslator
    {
        public static MstPvpRewardGroupModel ToMstPvpRewardGroupModel(
            MstPvpRewardGroupData groupData, 
            IReadOnlyList<MstPvpRewardData> rewardData)
        {
            return new MstPvpRewardGroupModel(
                new MasterDataId(groupData.Id),
                new MasterDataId(groupData.MstPvpId),
                rewardData.Select(ToMstPvpRewardModel).ToList(),
                groupData.RewardCategory,
                new PvpRewardConditionValue(groupData.ConditionValue));
        }

        static MstPvpRewardModel ToMstPvpRewardModel(MstPvpRewardData data)
        {
            return new MstPvpRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstPvpRewardGroupId),
                data.ResourceType,
                string.IsNullOrEmpty(data.ResourceId) ? 
                    MasterDataId.Empty : 
                    new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount));
        }
    }
}