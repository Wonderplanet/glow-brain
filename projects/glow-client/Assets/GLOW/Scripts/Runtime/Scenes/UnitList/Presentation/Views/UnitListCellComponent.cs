using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UnitList.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitList.Presentation.Views
{
    public class UnitListCellComponent : UICollectionViewCell
    {
        [SerializeField] CharacterLongIconComponent _characterIcon;
        [SerializeField] Button _button;
        [SerializeField] GameObject _badge;

        public Button Button => _button;
        // チュートリアルで使用
        public UserDataId UserUnitId {get; private set; }

        public void Setup(UnitListCellViewModel viewModel)
        {
            UserUnitId = viewModel.UserUnitId;

            _characterIcon.Setup(viewModel.CharacterIcon, false, viewModel.SortType);
            _badge.SetActive(viewModel.NotificationBadge.Value);
        }
    }
}
