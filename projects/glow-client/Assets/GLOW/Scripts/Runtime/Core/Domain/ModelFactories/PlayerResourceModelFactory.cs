using Cysharp.Text;
using GLOW.Core.Domain.Const;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Domain.ModelFactories
{
    public class PlayerResourceModelFactory : IPlayerResourceModelFactory
    {
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }

        PlayerResourceModel IPlayerResourceModelFactory.Create(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount)
        {
            return Create(
                type,
                id,
                amount,
                RewardCategory.Always,
                AcquiredFlag.False,
                StageClearTime.Empty);
        }

        PlayerResourceModel IPlayerResourceModelFactory.Create(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            RewardCategory rewardCategory)
        {
            return Create(
                type,
                id,
                amount,
                rewardCategory,
                AcquiredFlag.False,
                StageClearTime.Empty);
        }

        PlayerResourceModel IPlayerResourceModelFactory.Create(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            RewardCategory rewardCategory,
            AcquiredFlag acquiredFlag)
        {
            return Create(
                type,
                id,
                amount,
                rewardCategory,
                acquiredFlag,
                StageClearTime.Empty);
        }

        PlayerResourceModel IPlayerResourceModelFactory.Create(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            RewardCategory rewardCategory,
            AcquiredFlag acquiredFlag,
            StageClearTime clearTime)
        {
            return Create(type, id, amount, rewardCategory, acquiredFlag, clearTime);
        }

        PlayerResourceModel IPlayerResourceModelFactory.Create(PreConversionResourceModel model)
        {
            if(model.IsEmpty()) return PlayerResourceModel.Empty;

            return Create(
                model.ResourceType,
                model.ResourceId,
                model.ResourceAmount.ToPlayerResourceAmount(),
                RewardCategory.Always,
                AcquiredFlag.False,
                StageClearTime.Empty);
        }

        PlayerResourceModel Create(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            RewardCategory rewardCategory,
            AcquiredFlag isAcquired,
            StageClearTime clearTime)
        {
            var rarity = Rarity.R;
            var groupSortOrder = PlayerResourceGroupSortOrder.MaxValue;
            var sortOrder = SortOrder.MaxValue;
            var assetKey = PlayerResourceAssetKey.Empty;
            var resourceName = PlayerResourceName.Empty;
            var resourceDescription = PlayerResourceDescription.Empty;

            switch (type)
            {
                case ResourceType.Item:
                    var mstItem = MstItemDataRepository.GetItem(id);
                    rarity = mstItem.Rarity;
                    groupSortOrder = PlayerResourceModelConst.GetGroupSortOrder(mstItem.Type);
                    sortOrder = mstItem.SortOrder;
                    assetKey = mstItem.ItemAssetKey.ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceName.TranslateFromItemName(mstItem.Name);
                    resourceDescription = PlayerResourceDescription.TranslateFromItemDescription(mstItem.Description);
                    break;
                case ResourceType.ArtworkFragment:
                    var artworkFragment = MstArtworkFragmentDataRepository.GetArtworkFragment(id);
                    rarity = artworkFragment.Rarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ArtworkFragmentItemGroupSortOrder;
                    sortOrder = new SortOrder(artworkFragment.Position.Value);
                    assetKey = ArtworkFragmentAssetKey.ToArtworkFragmentAssetKey(artworkFragment.AssetNum).ToPlayerResourceAssetKey();
                    break;
                case ResourceType.Unit:
                    var mstCharacter = MstCharacterDataRepository.GetCharacter(id);
                    rarity = mstCharacter.Rarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.CharacterGroupSortOrder;
                    sortOrder = mstCharacter.SortOrder;
                    assetKey = mstCharacter.AssetKey.ToPlayerResourceAssetKey();
                    amount = PlayerResourceAmount.Empty;
                    resourceName = mstCharacter.Name.ToPlayerResourceName();
                    break;
                case ResourceType.Exp:
                    rarity = SpecificRarityConstants.UserExpRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.UserExpSortOrder;
                    assetKey = new UserExpAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.UserExpName;
                    resourceDescription = PlayerResourceModelConst.UserExpDescription;
                    break;
                case ResourceType.FreeDiamond:
                    rarity = SpecificRarityConstants.DiamondRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.FreeDiamondSortOrder;
                    assetKey = new DiamondAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.DiamondName;
                    resourceDescription = PlayerResourceModelConst.DiamondDescription;
                    break;
                case ResourceType.PaidDiamond:
                    rarity = SpecificRarityConstants.DiamondRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.PaidDiamondSortOrder;
                    assetKey = new DiamondAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.DiamondName;
                    resourceDescription = PlayerResourceModelConst.DiamondDescription;
                    break;
                case ResourceType.Coin:
                    rarity = SpecificRarityConstants.CoinRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.CoinSortOrder;
                    assetKey = new CoinAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.CoinName;
                    resourceDescription = PlayerResourceModelConst.CoinDescription;
                    break;
                case ResourceType.IdleCoin:
                    rarity = SpecificRarityConstants.CoinRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.CoinSortOrder;
                    assetKey = new CoinAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.CoinName;
                    resourceDescription = PlayerResourceModelConst.CoinDescription;
                    break;
                case ResourceType.MissionBonusPoint:
                    rarity = SpecificRarityConstants.MissionBonusPointRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ExpAndCurrencyGroupSortOrder;
                    sortOrder = SortOrder.MissionBonusPointSortOrder;
                    resourceName = new PlayerResourceName(
                        ZString.Format("{0}{1}",GetMissionBonusPointPrefix(id), PlayerResourceModelConst.MissionBonusPointName));
                    resourceDescription = PlayerResourceModelConst.MissionBonusDescription;
                    assetKey = CreateMissionBonusPointAssetKey(id);
                    break;
                case ResourceType.Emblem:
                    var emblem = MstEmblemRepository.GetMstEmblemFirstOrDefault(id);
                    rarity = SpecificRarityConstants.EmblemRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.EmblemGroupSortOrder;
                    sortOrder = SortOrder.EmblemSortOrder;
                    assetKey = emblem.AssetKey.ToPlayerResourceAssetKey();
                    resourceName = emblem.Name.ToPlayerResourceName();
                    break;
                case ResourceType.Stamina:
                    rarity = SpecificRarityConstants.StaminaRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.StaminaGroupSortOrder;
                    sortOrder = SortOrder.StaminaSortOrder;
                    assetKey = new StaminaAssetKey().ToPlayerResourceAssetKey();
                    resourceName = PlayerResourceModelConst.StaminaName;
                    resourceDescription = PlayerResourceModelConst.StaminaDescription;
                    break;
                case ResourceType.Artwork:
                    var artwork = MstArtworkDataRepository.GetArtwork(id);
                    rarity = SpecificRarityConstants.ArtworkRarity;
                    groupSortOrder = PlayerResourceGroupSortOrder.ArtworkItemGroupSortOrder;
                    sortOrder = SortOrder.ArtworkSortOrder;
                    assetKey = ArtworkAssetKey.ArtworkIconAssetKey.ToPlayerResourceAssetKey();
                    resourceName = artwork.Name.ToPlayerResourceName();
                    resourceDescription = artwork.Description.ToPlayerResourceDescription();
                    break;
            }

            return new PlayerResourceModel(
                type,
                id,
                rarity,
                resourceName,
                resourceDescription,
                groupSortOrder,
                sortOrder,
                assetKey,
                amount,
                rewardCategory,
                new PlayerResourceAcquiredFlag(isAcquired),
                clearTime);
        }

        PlayerResourceGroupSortOrder GetGroupSortOrder(ItemType type)
        {
            return type switch
            {
                ItemType.RankUpMaterial => PlayerResourceGroupSortOrder.CharacterRankUpMaterialGroupSortOrder,
                ItemType.CharacterFragment => PlayerResourceGroupSortOrder.CharacterFragmentGroupSortOrder,
                ItemType.StageMedal => PlayerResourceGroupSortOrder.StageMedalGroupSortOrder,
                _ => PlayerResourceGroupSortOrder.ItemGroupSortOrder,
            };
        }

        PlayerResourceAssetKey CreateMissionBonusPointAssetKey(MasterDataId masterDataId)
        {
            PlayerResourceAssetKey assetKey = PlayerResourceAssetKey.Empty;

            if (masterDataId == PlayerResourceConst.BeginnerBonusPointMasterDataId)
            {
                assetKey = new MissionBonusPointAssetKey().ToBeginnerMissionBonusPointResourceAssetKey();
            }
            else if (masterDataId == PlayerResourceConst.DailyBonusPointMasterDataId)
            {
                assetKey = new MissionBonusPointAssetKey().ToDailyMissionBonusPointResourceAssetKey();
            }
            else if (masterDataId == PlayerResourceConst.WeeklyBonusPointMasterDataId)
            {
                assetKey = new MissionBonusPointAssetKey().ToWeeklyMissionBonusPointResourceAssetKey();
            }

            return assetKey;
        }

        string GetMissionBonusPointPrefix(MasterDataId masterDataId)
        {
            if (masterDataId == PlayerResourceConst.BeginnerBonusPointMasterDataId) return "初心者";
            if (masterDataId == PlayerResourceConst.DailyBonusPointMasterDataId) return "デイリー";
            if (masterDataId == PlayerResourceConst.WeeklyBonusPointMasterDataId) return "ウィークリー";

            return "";
        }
    }
}
