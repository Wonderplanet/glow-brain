using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesArtworkListCell : MonoBehaviour
    {
        [SerializeField] ArtworkFragmentThumbnailComponent _artworkFragmentThumbnail;
        [SerializeField] Button _button;
        [SerializeField] GameObject _usingLabelObj;
        [SerializeField] UIObject _badge;

        public void Setup(EncyclopediaArtworkListCellViewModel viewModel, Action<MasterDataId> onSelectArtworkAction)
        {
            _artworkFragmentThumbnail.Setup(viewModel.FragmentPanelViewModel);
            _usingLabelObj.SetActive(viewModel.IsUsing.Value);
            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(() => onSelectArtworkAction?.Invoke(viewModel.MstArtworkId));
            _badge.Hidden = !viewModel.NewBadge;
        }
    }
}
