using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public class BattleStartButtonOverlappingUIComponent : MonoBehaviour
    {
        [Header("編成・Start/特別ルール")]
        [SerializeField] GameObject _specialRuleButton;
        [Header("編成・Start/スタミナブースト")]
        [SerializeField] UIObject _staminaBoostBalloon;
        [SerializeField] UIObject _staminaBoostFirstClearBalloon;
        [Header("編成・Start/ステージ挑戦表示")]
        [SerializeField] UIObject _tryStageText;


        bool _isTryStageTextVisible;
        public bool IsTryStageTextVisible { get; set; }
        bool _isSpecialRuleButtonVisible;
        StaminaBoostBalloonType _staminaBoostBalloonType = StaminaBoostBalloonType.None;
        CancellationTokenSource _overlappingUICancellation;

        public void SetVisibleSpecialRuleButton(bool isVisible)
        {
            SetOverlappingUIParameters(isVisible, null);
        }

        public void SetStaminaBoostBalloon(StaminaBoostBalloonType staminaBoostBalloonType)
        {
            SetOverlappingUIParameters(null, staminaBoostBalloonType);
        }

        public void SetOverlappingUIParameters(
            bool? isSpecialRuleButtonVisible,
            StaminaBoostBalloonType? staminaBoostBalloonType)
        {
            if (isSpecialRuleButtonVisible.HasValue)
            {
                _isSpecialRuleButtonVisible = isSpecialRuleButtonVisible.Value;
            }

            if (staminaBoostBalloonType.HasValue)
            {
                _staminaBoostBalloonType = staminaBoostBalloonType.Value;
            }
        }

        // ローテション向けの前準備の副作用処理
        public void InitializeOverlappingUIDisplayAnimation()
        {
            ClearOverlappingCancellation();

            // すべてのUIを初期状態にリセット
            ResetOverlappingUI(_tryStageText.gameObject);
            ResetOverlappingUI(_specialRuleButton);
            ResetOverlappingUI(_staminaBoostBalloon.gameObject);
            ResetOverlappingUI(_staminaBoostFirstClearBalloon.gameObject);
        }

        void ResetOverlappingUI(GameObject uiObject)
        {
            var targetCanvasGroup = uiObject.GetComponent<CanvasGroup>();
            targetCanvasGroup.DOKill();
            targetCanvasGroup.alpha = 0f;
            uiObject.SetActive(false);
        }

        // ローテーション処理開始
        public void StartRotateOverlappingUIAnimationIfNeeded()
        {
            var visibleUIs = GetVisibleUIs();
            if (visibleUIs.Count == 0)
            {
                return;
            }

            if (visibleUIs.Count == 1)
            {
                var targetCanvasGroup = visibleUIs[0].GetComponent<CanvasGroup>();
                targetCanvasGroup.alpha = 1f;
                visibleUIs[0].SetActive(true);
                return;
            }

            // 複数ある場合はローテーション表示
            _overlappingUICancellation = new CancellationTokenSource();
            RotateOverlappingUIAsync(visibleUIs, _overlappingUICancellation.Token).Forget();
        }


        async UniTaskVoid RotateOverlappingUIAsync(List<GameObject> visibleUIs, CancellationToken cancellationToken)
        {
            const float displayDuration = 3f;
            const float fadeDuration = 0.5f;

            var currentIndex = 0;
            var isFirstDisplay = true;

            try
            {
                while (!cancellationToken.IsCancellationRequested)
                {
                    var currentUI = visibleUIs[currentIndex];

                    // GameObjectが破棄されていないかチェック
                    if (currentUI == null)
                    {
                        break;
                    }

                    currentUI.SetActive(true);

                    var targetCanvasGroup = currentUI.GetComponent<CanvasGroup>();

                    await FadeInOverlappingUI(targetCanvasGroup, isFirstDisplay, fadeDuration, cancellationToken);
                    isFirstDisplay = false;

                    await UniTask.Delay((int)(displayDuration * 1000), cancellationToken: cancellationToken);

                    // フェードアウト前に再度チェック(Delay中に破棄される可能性があるため)
                    if (currentUI == null || cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    await targetCanvasGroup
                        .DOFade(0f, fadeDuration)
                        .SetEase(Ease.InOutQuad)
                        .ToUniTask(cancellationToken: cancellationToken);

                    currentUI.SetActive(false);

                    currentIndex = (currentIndex + 1) % visibleUIs.Count;
                }
            }
            catch (System.OperationCanceledException)
            {
                // キャンセル時は何もしない（UpdateOverlappingUIDisplayでリセット済み）
            }
        }

        async UniTask FadeInOverlappingUI(
            CanvasGroup targetCanvasGroup,
            bool isFirstDisplay,
            float fadeDuration,
            CancellationToken cancellationToken)
        {
            if (isFirstDisplay)
            {
                // 最初はパッと表示
                targetCanvasGroup.alpha = 1f;
            }
            else
            {
                // 2回目以降はフェードイン
                targetCanvasGroup.alpha = 0f;

                await targetCanvasGroup
                    .DOFade(1f, fadeDuration)
                    .SetEase(Ease.InOutQuad)
                    .ToUniTask(cancellationToken: cancellationToken);
            }
        }

        List<GameObject> GetVisibleUIs()
        {
            var result = new List<GameObject>();

            if (_isTryStageTextVisible)
            {
                result.Add(_tryStageText.gameObject);
            }

            if (_isSpecialRuleButtonVisible)
            {
                result.Add(_specialRuleButton);
            }

            if (_staminaBoostBalloonType == StaminaBoostBalloonType.DefaultBalloon)
            {
                result.Add(_staminaBoostBalloon.gameObject);
            }

            if (_staminaBoostBalloonType == StaminaBoostBalloonType.FirstClearBalloon)
            {
                result.Add(_staminaBoostFirstClearBalloon.gameObject);
            }
            return result;
        }

        void OnDestroy()
        {
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
        }

        void ClearOverlappingCancellation()
        {
            _overlappingUICancellation?.Cancel();
            _overlappingUICancellation?.Dispose();
            _overlappingUICancellation = null;
        }
    }
}
