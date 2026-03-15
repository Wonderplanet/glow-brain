using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AdventBattleRewardList.Domain.Model
{
    public interface IAdventBattlePersonalRewardModel
    {
        MasterDataId Id { get; }
        IReadOnlyList<PlayerResourceModel> Rewards { get; }
        public AdventBattleRewardCategory RewardCategory { get; }
    }
}