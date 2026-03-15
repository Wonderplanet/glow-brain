using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeUpAnimView : UIView
    {
        [SerializeField] IconGrade _currentGradeIcon;
        [SerializeField] IconGrade _nextGradeIcon;
        [SerializeField] GameObject _artworkFrameObject;
        [SerializeField] UIText _artworkEffectText;

        [Header("額縁テキスト")]
        [SerializeField] UIText _artworkFrameText;

        [Header("閉じるボタン")]
        [SerializeField] UIObject _closeButton;
        [SerializeField] UIText _closeText;

        const string ArtworkFrameTextFormat = "「{0}」の原画の額縁GET!!";

        public void Setup(ArtworkGradeUpAnimViewModel viewModel)
        {
            _closeButton.gameObject.SetActive(false);

            _currentGradeIcon.SetGrade(viewModel.BeforeGradeLevel);
            _nextGradeIcon.SetGrade(viewModel.AfterGradeLevel);
            _artworkEffectText.SetText(viewModel.EffectDescription.Value);
            _artworkFrameObject.SetActive(viewModel.IsGradeMaxLimit);
            _artworkFrameText.SetText(ArtworkFrameTextFormat, viewModel.ArtworkName.Value);
        }

        public void AnimationEnded()
        {
            _closeButton.IsVisible = true;
            _closeText.IsVisible = true;
        }
    }
}
