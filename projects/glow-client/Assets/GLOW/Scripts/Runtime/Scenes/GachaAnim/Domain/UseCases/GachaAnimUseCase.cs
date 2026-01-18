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
            var rarities = new List<Rarity>();

            foreach (var model in resultCache)
            {
                var masterDataId = model.ResourceId;
                var resourceType = model.ResourceType;
                var resourceAmount = model.ResourceAmount;
                var unitName = CharacterName.Empty;
                var rarity = Rarity.R;
                var role = CharacterUnitRoleType.None;
                var color = CharacterColor.None;
                var seriesAssetKey = SeriesAssetKey.Empty;
                var unitAssetKey = UnitAssetKey.Empty;
                var speechBalloonText = SpeechBalloonText.Empty;
                var itemName = ItemName.Empty;
                var itemAssetKey = ItemAssetKey.Empty;
                var production = GashaAnimProduction.Empty;
                var isNewUnitBadge = new IsNewUnitBadge(model.ResourceType == ResourceType.Unit);

                // アイテムに変換されている重複ユニットはユニットの方の情報を取得
                if (model.PreConversionResource.ResourceType == ResourceType.Unit)
                {
                    masterDataId = model.PreConversionResource.ResourceId;
                    resourceType = model.PreConversionResource.ResourceType;
                    resourceAmount = model.PreConversionResource.ResourceAmount;
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

                    // 演出用の最大レアリティ計算
                    if(rarity > maxRarity) maxRarity = rarity;
                }

                // アイテム情報を取得
                if (resourceType == ResourceType.Item)
                {
                    var mstItemData = MstItemDataRepository.GetItem(masterDataId);
                    itemName = mstItemData.Name;
                    itemAssetKey = mstItemData.ItemAssetKey;
                }

                production = GashaAnimProduction.EvaluatePromotion(resourceType, rarity);

                rarities.Add(rarity);

                resultModels.Add(
                    new GachaAnimResultModel(
                        resourceType,
                        production,
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
                            resourceAmount.ToPlayerResourceAmount(), 
                            itemAssetKey),
                        isNewUnitBadge
                    ));
            }

            // ガシャ演出のスタート時レアリティの抽選を行う
            var startRarity = GachaAnimStartRarityEvaluator.GetStartRarity(rarities, maxRarity);

            return new GachaAnimUseCaseModel(resultModels, startRarity, maxRarity);
        }
    }
}
