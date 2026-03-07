using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaLineupDialog.Presentation.ViewModels;
using TMPro;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaLineupDialog.Presentation.Views.Components
{
    public class GachaLineupCellComponent : UIObject
    {
        [SerializeField] UIObject _whiteBG;
        [SerializeField] UIObject _grayBG;
        [SerializeField] PlayerResourceIconComponent _resourceIcon;
        [SerializeField] Button _iconButton;
        [SerializeField] TextMeshProUGUI _nameText;

        public void Setup(GachaLineupCellViewModel viewModel)
        {
            _iconButton.onClick.AddListener(() =>
            {
                viewModel.ClickIconEvent?.Invoke(viewModel.ResourceModel);
            });

            var name = viewModel.PlayerResourceIconViewModel.ResourceType == ResourceType.Unit
                ? viewModel.CharacterName.Value.ToString()
                : viewModel.ResourceName.Value.ToString();

            _resourceIcon.Setup(viewModel.PlayerResourceIconViewModel);
            
            _nameText.SetText(name);

            // 白/灰を交互に表示
            _whiteBG.Hidden = viewModel.NumberParity.IsOdd;
            _grayBG.Hidden = viewModel.NumberParity.IsEven;
        }
    }
}
