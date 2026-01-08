using Cysharp.Text;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect.Component
{
    public class QuestDifficultyButtonComponent : UIObject
    {
        [Header("Cell本体")]
        [SerializeField] RectTransform _rootRectTransform;
        [Header("原画のかけら数(獲得数/獲得可能数")]
        [SerializeField] UIObject _artworkFragmentObject;
        [SerializeField] UIText _artworkFragmentText;
        [SerializeField] UIObject _artworkFragmentNothingButton;
        [Header("選択状態を表す枠")]
        [SerializeField] UIObject _choiceObject;
        [Header("未開放")]
        [SerializeField] GameObject _unReleaseGameObject;
        [Header("開放アニメーション")]
        [SerializeField] TutorialQuestDifficultyHardReleaseAnimation _tutorialHardReleaseAnimation;
        [Header("チュートリアル用ボタン制御")]
        [SerializeField] Button _difficultyButton;
        
        const string ArtworkFragmentTextFormat = "{0} / {1}";
        Difficulty _difficulty = Difficulty.Normal;

        readonly float _selectedButtonScale = 0.85f;
        Tween _tween;

        public void Setup(Difficulty currentDifficulty, QuestDifficultyItemViewModel viewModel)
        {
            SetArtworkFragmentText(viewModel.AcquiredArtworkFragmentNum, viewModel.GettableArtworkFragmentNum);
            SetReleaseStatus(viewModel.DifficultyOpenStatus);

            _difficulty = viewModel.Difficulty;
            
            var isChoice = (currentDifficulty == _difficulty) && 
                           (viewModel.DifficultyOpenStatus == QuestDifficultyOpenStatus.Released);
            
            _choiceObject.Hidden = !isChoice;
            _rootRectTransform.localScale = isChoice ? Vector3.one : Vector3.one * _selectedButtonScale;
        }

        public void PlayScaleAnimation(Difficulty currentDifficulty)
        {
            var isChoice = currentDifficulty == _difficulty;
            _choiceObject.Hidden = !isChoice;
            
            _tween?.Kill();
            _tween = _rootRectTransform
                .DOScale(isChoice ? Vector3.one : Vector3.one * _selectedButtonScale, 0.15f)
                .SetEase(Ease.OutExpo)
                .SetLink(gameObject);
        }

        public void PlayReleaseAnimation()
        {
            _tutorialHardReleaseAnimation.gameObject.SetActive(true);
            _tutorialHardReleaseAnimation.ShowAnimation();
        }

        public void SetButtonEnabled(bool isEnabled)
        {
            _difficultyButton.enabled = isEnabled;
        }

        void SetArtworkFragmentText(ArtworkFragmentNum acquired, ArtworkFragmentNum gettable)
        {
            var isGettable = !gettable.IsZero();
            _artworkFragmentObject.Hidden = !isGettable;
            _artworkFragmentNothingButton.Hidden = isGettable;
            if (isGettable)
            {
                _artworkFragmentText.SetText(ZString.Format(ArtworkFragmentTextFormat, acquired.Value, gettable.Value));
            }
        }

        void SetReleaseStatus(QuestDifficultyOpenStatus status)
        {
            _unReleaseGameObject.SetActive(status == QuestDifficultyOpenStatus.NotRelease);
        }
    }
}
