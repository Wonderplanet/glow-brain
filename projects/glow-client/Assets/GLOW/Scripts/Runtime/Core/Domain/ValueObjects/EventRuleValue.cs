using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EventRuleValue(ObscuredString Value)
    {
        public static EventRuleValue Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public int ToInt()
        {
            if(Int32.TryParse(Value, out var result))
            {
                return result;
            }

            return 0;
        }

        public bool IsTrue()
        {
            return Value == "1";
        }

        public bool IsFalse()
        {
            return Value == "0";
        }

        public NoContinueFlag ToNoContinueFlag()
        {
            if (IsEmpty()) return NoContinueFlag.Empty;

            return new NoContinueFlag(IsTrue());
        }

        public MasterDataId ToSeriesId()
        {
            return new MasterDataId(Value);
        }

        public Rarity ToRarity()
        {
            return Enum.Parse<Rarity>(Value);
        }

        public CharacterUnitRoleType ToUnitRoleType()
        {
            return Enum.Parse<CharacterUnitRoleType>(Value);
        }

        public InGameSpecialRuleUnitAmount ToUnitAmount()
        {
            var value = int.Parse(Value);
            return new InGameSpecialRuleUnitAmount(value);
        }

        public CharacterAttackRangeType ToAttackRangeType()
        {
            return Enum.Parse<CharacterAttackRangeType>(Value);
        }

        public InGameSpecialRuleStartOutpostHp ToStartOutpostHp()
        {
            var value = int.Parse(Value);
            return new InGameSpecialRuleStartOutpostHp(value);
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}
