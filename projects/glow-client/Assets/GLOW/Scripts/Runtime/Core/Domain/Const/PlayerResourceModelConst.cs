using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.ModelFactories
{
    public static class PlayerResourceModelConst
    {
        public static readonly PlayerResourceName DiamondName = new("プリズム");
        public static readonly PlayerResourceName CoinName = new("コイン");
        public static readonly PlayerResourceName MissionBonusPointName = new("ミッションポイント");
        public static readonly PlayerResourceName UserExpName = new("リーダーEXP");
        public static readonly PlayerResourceName StaminaName = new("スタミナ");

        public static readonly PlayerResourceDescription DiamondDescription = new("様々な奇跡を呼び起こす可能性のある貴重な石。");
        public static readonly PlayerResourceDescription CoinDescription = new("様々な箇所で使用することのできるアイテム。");
        public static readonly PlayerResourceDescription MissionBonusDescription = new("獲得した量に応じてミッションの報酬を受け取れるようになるポイント。");
        public static readonly PlayerResourceDescription UserExpDescription = new("獲得した量に応じてリーダーのレベルが上昇する経験値。");
        public static readonly PlayerResourceDescription StaminaDescription = new("バトルに挑戦するために必要なスタミナ。");

        public static PlayerResourceGroupSortOrder GetGroupSortOrder(ItemType type)
        {
            return type switch
            {
                ItemType.RankUpMaterial => PlayerResourceGroupSortOrder.CharacterRankUpMaterialGroupSortOrder,
                ItemType.CharacterFragment => PlayerResourceGroupSortOrder.CharacterFragmentGroupSortOrder,
                ItemType.StageMedal => PlayerResourceGroupSortOrder.StageMedalGroupSortOrder,
                _ => PlayerResourceGroupSortOrder.ItemGroupSortOrder,
            };
        }
    }
}