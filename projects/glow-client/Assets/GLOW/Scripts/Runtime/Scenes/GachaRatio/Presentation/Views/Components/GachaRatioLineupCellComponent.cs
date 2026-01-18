using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using TMPro;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaRatio.Presentation.Views.Components
{
    public class GachaRatioLineupCellComponent : UIObject
    {
        [SerializeField] UIObject _whiteBG;
        [SerializeField] UIObject _grayBG;
        [SerializeField] PlayerResourceIconComponent _resourceIcon;
        [SerializeField] Button _iconButton;
        [SerializeField] TextMeshProUGUI _nameText;
        [SerializeField] TextMeshProUGUI _ratioText;

        public void Setup(GachaRatioLineupCellViewModel viewModel)
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

            _ratioText.SetText(viewModel.OutputRatio.ToShowText());
            //_ratioText.faceColor = Color.red;
        }
    }
}
