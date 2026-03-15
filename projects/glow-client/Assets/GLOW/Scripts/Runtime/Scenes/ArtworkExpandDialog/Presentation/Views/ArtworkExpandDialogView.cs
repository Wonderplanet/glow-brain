using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画拡大ダイアログ
    /// </summary>
    public class ArtworkExpandDialogView : UIView
    {
        [SerializeField] UIText _title;
        [SerializeField] UIImage _artwork;
        [SerializeField] GameObject _artworkGrrayout;
        [SerializeField] UIText _description;
        [SerializeField] GameObject _descriptionGrrayout;
        [SerializeField] ArtworkFragmentPanelComponent _artworkFragmentPanelComponent;

        public void SetUpFromEncyclopedia(ArtworkExpandDialogViewModel viewModel, bool isLock)
        {
            // 図鑑から開いた場合はグレイアウト非表示
            _artworkGrrayout.SetActive(false);

            _descriptionGrrayout.SetActive(isLock);

            _title.SetText(viewModel.Name.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_artwork.Image, viewModel.AssetPath.Value);
            _description.SetText(viewModel.Description.Value);
            _artworkFragmentPanelComponent.Setup(viewModel.ArtworkFragmentPanelViewModel);
        }

        public void SetUpFromExchangeShop(ArtworkExpandDialogViewModel viewModel, bool isLock)
        {
            // グレーアウトの際には使用しない
            _artworkFragmentPanelComponent.gameObject.SetActive(false);

            _artworkGrrayout.SetActive(isLock);
            _descriptionGrrayout.SetActive(isLock);

            _title.SetText(viewModel.Name.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_artwork.Image, viewModel.AssetPath.Value);
            _description.SetText(viewModel.Description.Value);
        }
    }
}
