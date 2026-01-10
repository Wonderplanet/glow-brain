using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Constants;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkFragmentListCell : UICollectionViewCell
    {
        static readonly string Challenge = "Challenge";
        static readonly string Unreleased = "Unreleased";
        static readonly string OutOfTerm = "OutOfTerm";

        [SerializeField] UIText _name;
        [SerializeField] UIText _conditionText;
        [SerializeField] GameObject _blockMask;

        [Header("かけらアイコン")]
        [SerializeField] UIImage _fragmentIcon;
        [SerializeField] IconRarityFrame _rarityFrame;

        [Header("ボタン")]
        [SerializeField] Button _challengeButton;
        [SerializeField] Button _unreleasedButton;
        [SerializeField] Button _outOfTermButton;
        [SerializeField] GameObject _clearedIcon;

        protected override void Awake()
        {
            base.Awake();
            AddButton(_challengeButton, Challenge);
            AddButton(_unreleasedButton, Unreleased);
            AddButton(_outOfTermButton, OutOfTerm);
        }

        public void Setup(EncyclopediaArtworkFragmentListCellViewModel viewModel)
        {
            _name.SetText(viewModel.FragmentName.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_fragmentIcon.Image, viewModel.AssetPath.Value);
            _rarityFrame.Setup(IconRarityFrameType.Item, viewModel.FragmentRarity);
            _conditionText.SetText(viewModel.DropConditionText.Value);
            SetButtonState(viewModel.StatusFlags);
        }

        void SetButtonState(ArtworkFragmentStatusFlags statusFlags)
        {
            _clearedIcon.SetActive(statusFlags.IsCleared);

            _unreleasedButton.gameObject.SetActive(false);
            _outOfTermButton.gameObject.SetActive(false);
            _challengeButton.gameObject.SetActive(false);
            switch (statusFlags)
            {
                case {IsCleared: false, IsUnReleaseQuest: true, IsOutOfTermQuest: false}:
                    _unreleasedButton.gameObject.SetActive(true);
                    break;
                case {IsCleared: false, IsOutOfTermQuest: true}:
                    _outOfTermButton.gameObject.SetActive(true);
                    break;
                case {IsEnableChallenge: true}:
                    _challengeButton.gameObject.SetActive(true);
                    break;
            }

            _blockMask.SetActive(!statusFlags.IsEnableChallenge);
        }
    }
}
