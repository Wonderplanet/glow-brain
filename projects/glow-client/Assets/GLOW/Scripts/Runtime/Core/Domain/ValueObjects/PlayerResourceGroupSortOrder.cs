
using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerResourceGroupSortOrder(ObscuredInt Value) : IComparable<PlayerResourceGroupSortOrder>
    {
        public static PlayerResourceGroupSortOrder MaxValue { get; } = new(int.MaxValue);

        public static readonly PlayerResourceGroupSortOrder ExpAndCurrencyGroupSortOrder = new(1);
        public static readonly PlayerResourceGroupSortOrder CharacterRankUpMaterialGroupSortOrder = new(2);
        public static readonly PlayerResourceGroupSortOrder ArtworkFragmentItemGroupSortOrder = new(3);
        public static readonly PlayerResourceGroupSortOrder CharacterGroupSortOrder = new(4);
        public static readonly PlayerResourceGroupSortOrder CharacterFragmentGroupSortOrder = new(5);
        public static readonly PlayerResourceGroupSortOrder StageMedalGroupSortOrder = new(6);
        public static readonly PlayerResourceGroupSortOrder EmblemGroupSortOrder = new(7);
        public static readonly PlayerResourceGroupSortOrder ItemGroupSortOrder = new(8);
        public static readonly PlayerResourceGroupSortOrder StaminaGroupSortOrder = new(9);
        public static readonly PlayerResourceGroupSortOrder ArtworkItemGroupSortOrder = new(10);
        
        public int CompareTo(PlayerResourceGroupSortOrder other) => Value.CompareTo(other.Value);
    }
}
