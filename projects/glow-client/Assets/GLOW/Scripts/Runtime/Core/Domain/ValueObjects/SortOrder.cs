
using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SortOrder(ObscuredInt Value) : IComparable<SortOrder>
    {
        public static SortOrder MaxValue { get; } = new(int.MaxValue);
        public static SortOrder Empty { get; } = new(0);
        public static SortOrder Zero { get; } = new(0);

        public static readonly SortOrder PaidDiamondSortOrder = new(1);
        public static readonly SortOrder FreeDiamondSortOrder = new(2);
        public static readonly SortOrder UserExpSortOrder = new(3);
        public static readonly SortOrder CoinSortOrder = new(4);
        public static readonly SortOrder StaminaSortOrder = new(5);
        public static readonly SortOrder MissionBonusPointSortOrder = new(6);
        public static readonly SortOrder EmblemSortOrder = new(7);
        public static readonly SortOrder ArtworkSortOrder = new(8);

        public int CompareTo(SortOrder other) => Value.CompareTo(other.Value);
    }
}
