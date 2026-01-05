using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.LogModel
{
    public record PvpInGameEndBattleLogModel(
        StageClearTime ClearTime,
        Damage MaxDamage,
        IReadOnlyList<PartyStatusModel> PlayerPartyStatusModels,
        IReadOnlyList<PartyStatusModel> OpponentPartyStatusModels)
    {
        public static PvpInGameEndBattleLogModel Empty { get; } = new(
            StageClearTime.Empty,
            Damage.Empty,
            new List<PartyStatusModel>(),
            new List<PartyStatusModel>()
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}