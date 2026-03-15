using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkAcquisitionRouteCellView : UIObject
    {
        [SerializeField] UIText _acquisitionRouteName;

        public void Setup(ArtworkAcquisitionRouteCellViewModel viewModel)
        {
            var format = GetFormat(viewModel.Type);
            _acquisitionRouteName.SetText(format, viewModel.ArtworkAcquisitionRouteName.Value);
        }

        string GetFormat(ArtworkAcquisitionRouteType type)
        {
            return type switch
            {
                ArtworkAcquisitionRouteType.Fragment =>  "",
                ArtworkAcquisitionRouteType.UnitGrade =>  "「{0}」をグレード５まで強化",
                ArtworkAcquisitionRouteType.Shop =>  "ショップ",
                ArtworkAcquisitionRouteType.Exchange =>  "{0}交換所",
                ArtworkAcquisitionRouteType.Gasha =>  "ガシャ",
                ArtworkAcquisitionRouteType.PanelMission =>  "パネルミッション",
                ArtworkAcquisitionRouteType.EventMission =>  "イベントミッション",
                ArtworkAcquisitionRouteType.BoxGasha =>  "ボックスガシャ",

                _ =>  ""
            };
        }
    }
}
