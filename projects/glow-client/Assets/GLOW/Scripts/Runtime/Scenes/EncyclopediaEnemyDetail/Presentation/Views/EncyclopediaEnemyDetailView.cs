using GLOW.Core.Presentation.Components;
using GLOW.Modules.UnitAvatarPageView.Presentation.Views;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-2_ファントムキャラ表示
    /// </summary>
    public class EncyclopediaEnemyDetailView : UIView
    {
        [SerializeField] UnitAvatarPageListComponent _unitAvatarPageListComponent;
        [SerializeField] SeriesLogoComponent _seriesLogo;
        [SerializeField] UIText _unitName;
        [SerializeField] UIText _unitDescription;
        [SerializeField] ScrollRect _scrollRect;

        public UnitAvatarPageListComponent UnitAvatarPageListComponent => _unitAvatarPageListComponent;

        public void Setup(EncyclopediaEnemyDetailViewModel viewModel)
        {
            _unitName.SetText(viewModel.Name.Value);
            _seriesLogo.Setup(viewModel.SeriesLogoImagePath);
            _unitDescription.SetText(viewModel.Description.Value);
            _scrollRect.normalizedPosition = Vector2.up;
        }
    }
}
