using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public class OutpostEnhanceArtworkListCellComponent : UICollectionViewCell
    {
        [SerializeField] ArtworkFragmentThumbnailComponent _artworkFragmentThumbnail;
        [SerializeField] GameObject _selectObj;
        [SerializeField] UIButtonLongPress _longPress;
        [SerializeField] GameObject _badge;

        public UIButtonLongPress LongPress => _longPress;

        public MasterDataId MstArtworkId { get; private set; }

        public void Setup(OutpostEnhanceArtworkListCellViewModel viewModel)
        {
            MstArtworkId = viewModel.MstArtworkId;
            _artworkFragmentThumbnail.Setup(viewModel.ArtworkFragmentPanelViewModel);
            _selectObj.SetActive(viewModel.IsSelect);
            _badge.SetActive(viewModel.Badge.Value);
        }
    }
}
