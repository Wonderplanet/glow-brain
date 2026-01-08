using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaAnim.Domain.Model;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;

namespace GLOW.Scenes.GachaAnim.Presentation.Translator
{
    public class GachaAnimTranslator
    {
        public static GachaAnimViewModel ToTranslateGachaAnimViewModel(
            GachaAnimStartViewModel gachaAnimStartViewModel,
            List<GachaAnimResultViewModel> gachaAnimResultViewModelList
        )
        {
            return new GachaAnimViewModel(
                gachaAnimStartViewModel,
                gachaAnimResultViewModelList
            );
        }

        public static GachaAnimResultViewModel ToTranslateGachaAnimResultViewModel(GachaAnimResultModel model, IPlayerResourceModelFactory playerResourceModelFactory)
        {
            var resourceModel = playerResourceModelFactory.Create(model.ResourceType, model.ItemModel.Id, model.ItemModel.ResourceAmount);
            var icomModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(resourceModel);
            var itemName = resourceModel.Name;

            return new GachaAnimResultViewModel(
                model.ResourceType,
                model.UnitModel.CharacterUnitRoleType,
                model.UnitModel.CharacterColor,
                model.UnitModel.Rarity,
                new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(model.UnitModel.SeriesAssetKey.Value)),
                GachaAnimationUnitInfoAssetPath.FromAssetKey(model.UnitModel.UnitAssetKey),
                model.UnitModel.Name,
                icomModel.AssetPath,
                itemName,
                new GachaAnimNewFlg(model.IsNewUnitBadge.Value),
                UnitImageAssetPath.FromAssetKey(model.UnitModel.UnitAssetKey),
                model.UnitModel.SpeechBalloonText
                );
        }

        public static GachaAnimStartViewModel ToTranslateGachaAnimStartViewModel(Rarity startRarity, Rarity endRarity, List<GachaAnimIconInfo> iconCellInfos)
        {
            return new GachaAnimStartViewModel(
                startRarity,
                endRarity,
                iconCellInfos.Count,
                iconCellInfos
            );
        }
    }
}
