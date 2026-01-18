using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class QuestReleaseView : UIView
    {
        [SerializeField] Button _closeButton;
        [SerializeField] HomeMainQuestSymbolImage _symbolImage;
        [SerializeField] UIText _questNameText;
        [SerializeField] UIText _flavorText;
        [SerializeField] CanvasGroup _canvasGroup;
        public CanvasGroup CanvasGroup => _canvasGroup;
        public Button CloseButton => _closeButton;


        public string QuestImageAssetPath
        {
            set => _symbolImage.AssetPath = value;
        }

        public string QuestName
        {
            set => _questNameText.SetText(value);
        }

        public string FlavorText
        {
            set => _flavorText.SetText(value);
        }

        protected override void Awake()
        {
            _closeButton.interactable = false;
        }
    }
}
