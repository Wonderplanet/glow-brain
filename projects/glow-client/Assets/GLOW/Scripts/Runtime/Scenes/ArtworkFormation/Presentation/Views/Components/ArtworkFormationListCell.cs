using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public class ArtworkFormationListCell : UICollectionViewCell
    {
        [SerializeField] UIImage _artworkImage;
        [SerializeField] UIObject _assignedObject;
        [SerializeField] IconRarityImage _rarityImage;
        [SerializeField] IconGrade _gradeImage;
        [SerializeField] UIObject _gradeRoot;
        [SerializeField] ArtworkFragmentThumbnailComponent _artworkFragmentThumbnailComponent;
        [SerializeField] UIButtonLongPress _longPress;
        [SerializeField] UIObject _grayOutObject;

        public UIButtonLongPress LongPress => _longPress;
        public MasterDataId MstArtworkId { get; private set; }

        public void SetUp(ArtworkFormationListCellViewModel viewModel)
        {
            MstArtworkId = viewModel.MstArtworkId;

            // 原画の完成状態を設定し、原画の画像も設定する
            _artworkFragmentThumbnailComponent.Setup(viewModel.ArtworkFragmentPanelViewModel);
            // UISpriteUtil.LoadSpriteWithFade(_artworkImage.Image, viewModel.AssetPath.Value);
            _assignedObject.IsVisible = viewModel.IsAssigned;
            _rarityImage.Setup(viewModel.Rarity);

            // 原画が完成している場合は表示する
            _gradeRoot.IsVisible = viewModel.IsCompleted;

            _gradeImage.SetGrade(viewModel.Grade);

            // グレーアウト状態を反映
            _grayOutObject.IsVisible = viewModel.IsGrayOut;
        }
    }
}
