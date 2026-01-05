using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationMemberNextUnlockSlotModel(
        QuestName QuestName,
        Difficulty Difficulty,
        StageNumber StageNumber,
        PartyMemberSlotCount Count
    )
    {
        public static PartyFormationMemberNextUnlockSlotModel Empty { get; } = new (QuestName.Empty, Difficulty.Normal, StageNumber.Empty, PartyMemberSlotCount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
