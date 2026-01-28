using System;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceActionValue(ObscuredString Value)
    {
        public static AutoPlayerSequenceActionValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }

        public DeckUnitIndex ToDeckUnitIndex()
        {
            if (Int32.TryParse(Value, out int index))
            {
                return new DeckUnitIndex(index);
            }
            return DeckUnitIndex.Empty;
        }
        public AutoPlayerSequenceElementId ToAutoPlayerSequenceElementId()
        {
            return new AutoPlayerSequenceElementId(Value);
        }
        public AutoPlayerSequenceGroupId ToAutoPlayerSequenceGroupId()
        {
            return new AutoPlayerSequenceGroupId(Value);
        }
    }
}
