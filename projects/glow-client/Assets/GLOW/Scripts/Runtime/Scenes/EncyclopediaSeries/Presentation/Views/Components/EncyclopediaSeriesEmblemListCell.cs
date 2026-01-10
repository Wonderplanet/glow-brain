using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.Views
{
    public class EncyclopediaSeriesEmblemListCell : MonoBehaviour
    {
        [SerializeField] UIImage _emblemImage;
        [SerializeField] Button _button;
        [SerializeField] GameObject _lockedIcon;
        [SerializeField] UIObject _badge;

        public void Setup(EncyclopediaEmblemListCellViewModel viewModel, Action<MasterDataId> onSelectEmblemAction)
        {
            UISpriteUtil.LoadSpriteWithFade(_emblemImage.Image, viewModel.AssetPath.Value);
            _lockedIcon.SetActive(!viewModel.IsUnlocked.Value);
            _button.onClick.RemoveAllListeners();
            _button.onClick.AddListener(() => onSelectEmblemAction?.Invoke(viewModel.MstEmblemId));
            _badge.Hidden = !viewModel.NewBadge;
        }
    }
}
