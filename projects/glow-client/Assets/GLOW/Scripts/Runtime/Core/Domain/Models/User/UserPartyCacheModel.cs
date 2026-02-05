using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserPartyCacheModel(
        PartyNo PartyNo,
        PartyName PartyName,
        PartyMemberSlotCount SlotCount,
        IReadOnlyList<UserDataId> UserUnitIds)
    {
        //進行度でSlotCountが変化するので、必ずGetUnitList()から取るようにする
        IReadOnlyList<UserDataId> UserUnitIds { get; } = UserUnitIds;

        public static UserPartyCacheModel Empty { get; } = new (
            PartyNo.Empty,
            PartyName.Empty,
            PartyMemberSlotCount.Empty,
            new List<UserDataId>()
        );

        public static UserPartyCacheModel Create(UserPartyModel userPartyModel, PartyMemberSlotCount slotCount)
        {
            return new UserPartyCacheModel(
                userPartyModel.PartyNo,
                userPartyModel.PartyName,
                slotCount,
                userPartyModel.GetUnitList()
            );
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public IReadOnlyList<UserDataId> GetUnitList()
        {
            return UserUnitIds
                .Take(SlotCount.Value)
                .ToList();
        }
    }
}
