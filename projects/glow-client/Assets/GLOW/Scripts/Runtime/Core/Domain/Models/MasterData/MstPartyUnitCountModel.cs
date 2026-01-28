using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstPartyUnitCountModel(MasterDataId MstStageId, PartyMemberSlotCount Count) : IComparable
    {
        public static MstPartyUnitCountModel Empty { get; } = new(MasterDataId.Empty, PartyMemberSlotCount.Empty);

        int IComparable.CompareTo(object obj)
        {
            if (obj is MstPartyUnitCountModel other)
            {
                return MstStageId.CompareTo(other.MstStageId);
            }
            return 0;
        }

    }
}
