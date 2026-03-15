using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component
{
    public class QuestDifficultyButtonListComponent : UIObject
    {
        [SerializeField] QuestDifficultyButtonComponent _normalButton;
        [SerializeField] QuestDifficultyButtonComponent _hardButton;
        [SerializeField] QuestDifficultyButtonComponent _extraButton;
        [SerializeField] VerticalLayoutGroup _buttonLayoutGroup;

        public QuestDifficultyButtonComponent NormalButton => _normalButton;
        public QuestDifficultyButtonComponent HardButton => _hardButton;
        public QuestDifficultyButtonComponent ExtraButton => _extraButton;

        public void InitializeView()
        {
            _normalButton.InitializeView();
            _hardButton.InitializeView();
            _extraButton.InitializeView();
        }

        public void UpdateButtonLayoutGroup()
        {
            _buttonLayoutGroup.CalculateLayoutInputHorizontal();
            _buttonLayoutGroup.SetLayoutHorizontal();
        }
    }
}
