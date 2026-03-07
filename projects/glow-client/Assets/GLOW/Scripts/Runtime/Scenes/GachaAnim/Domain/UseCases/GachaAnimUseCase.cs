using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.GachaAnim.Domain.Evaluator;
using GLOW.Scenes.GachaAnim.Domain.Model;
using Zenject;

namespace GLOW.Scenes.GachaAnim.Domain.UseCases
{
    public class GachaAnimUseCase
    {
        [Inject] IGachaCacheRepository GachaCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGachaAnimStartRarityEvaluator GachaAnimStartRarityEvaluator { get; }

        public GachaAnimUseCaseModel GetGashaAnimUseCaseModel()
        {
            // キャッシュからガチャ結果を取得
            var resultCache = GachaCacheRepository.GetGachaResultModels();

            var resultModels = new List<GachaAnimResultModel>();
            var maxRarity = Rarity.R;
            var startRarities = new List<Rarity>();

            foreach (var model in resultCache)
            {
                var masterDataId = model.RewardModel.ResourceId;
                var resourceType = model.RewardModel.ResourceType;
                var resourceAmount = model.RewardModel.Amount;
                var unitName = CharacterName.Empty;
                var rarity = Rarity.R;
                var role = CharacterUnitRoleType.None;
                var color = CharacterColor.None;
                var seriesAssetKey = SeriesAssetKey.Empty;
                var unitAssetKey = UnitAssetKey.Empty;
                var speechBalloonText = SpeechBalloonText.Empty;
                var itemName = ItemName.Empty;
                var itemAssetKey = ItemAssetKey.Empty;
                var isNewUnitBadge = new IsNewUnitBadge(model.RewardModel.ResourceType == ResourceType.Unit);
                var displayRarity = Rarity.R;
                
                // アイテムに変換されている重複ユニットはユニットの方の情報を取得
                if (model.RewardModel.PreConversionResource.ResourceType == ResourceType.Unit)
                {
                    masterDataId = model.RewardModel.PreConversionResource.ResourceId;
                    resourceType = model.RewardModel.PreConversionResource.ResourceType;
                    resourceAmount = model.RewardModel.PreConversionResource.ResourceAmount.ToPlayerResourceAmount();
                }

                // ユニット情報を取得
                if (resourceType == ResourceType.Unit)
                {
                    var mstCharacterData = MstCharacterDataRepository.GetCharacter(masterDataId);
                    unitName = mstCharacterData.Name;
                    rarity = mstCharacterData.Rarity;
                    role = mstCharacterData.RoleType;
                    color = mstCharacterData.Color;
                    seriesAssetKey = mstCharacterData.SeriesAssetKey;
                    unitAssetKey = mstCharacterData.AssetKey;
                    speechBalloonText = mstCharacterData.SpeechBalloons
                                            .FirstOrDefault(speechBalloonModel => speechBalloonModel.ConditionType == SpeechBalloonConditionType.Summon)
                                            ?.SpeechBalloonText ?? SpeechBalloonText.Empty;
                    
                    // 昇格演出用の表示レアリティ計算
                    // GachaPrizeTypeがRegularの場合のみ昇格演出を行う（URの場合ランダムでレアリティを選出）
                    // それ以外（Fixed、MaxRarity、Pickup）の場合はレアリティが確定しているため昇格演出を行わない
                    if (model.GachaPrizeType == GachaPrizeType.Regular)
                    {
                        displayRarity = GachaAnimStartRarityEvaluator.GetDisplayRarityForUR(rarity);
                    }
                    else
                    {
                        // 確定枠、天井、ピックアップの場合は実際のレアリティをそのまま表示
                        displayRarity = rarity;
                    }
                    
                    // 演出用の最大レアリティ計算
                    if (displayRarity > maxRarity)
                    {
                        maxRarity = displayRarity;
                    }
                }

                // アイテム情報を取得
                if (resourceType == ResourceType.Item)
                {
                    var mstItemData = MstItemDataRepository.GetItem(masterDataId);
                    itemName = mstItemData.Name;
                    itemAssetKey = mstItemData.ItemAssetKey;
                }

                startRarities.Add(displayRarity);

                resultModels.Add(
                    new GachaAnimResultModel(
                        resourceType,
                        new GachaAnimUnitModel(
                            masterDataId, 
                            unitName, 
                            rarity, 
                            role, 
                            color, 
                            seriesAssetKey, 
                            unitAssetKey, 
                            speechBalloonText),
                        new GachaAnimItemModel(
                            masterDataId, 
                            itemName, 
                            rarity, 
                            resourceAmount, 
                            itemAssetKey),
                        isNewUnitBadge,
                        displayRarity
                    ));
            }

            // ガシャ演出のスタート時レアリティの抽選を行う
            var startRarity = GachaAnimStartRarityEvaluator.GetStartRarity(startRarities, maxRarity);

            return new GachaAnimUseCaseModel(resultModels, startRarity, maxRarity);
        }
        
        
    }
}
