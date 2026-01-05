using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using DG.Tweening;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

#if UNITY_EDITOR
using UnityEditor;
#endif

namespace GLOW.Core.Presentation.Components
{
    [ExecuteAlways]
    [RequireComponent(typeof(CanvasGroup))]
    public class ChildScaler : MonoBehaviour
    {
        [SerializeField] ChildScalerSetting _setting = new();
        
        [Header("スクリプタブル設定")] 
        [SerializeField] ChildScalerSettingPreset _settingPresetAsset;

        bool _isInitialized;
        CanvasGroup _canvasGroup;
        List<Tween> _tweenList = new ();
        
        public ChildScalerSettingPreset SettingPreset => _settingPresetAsset;

        void Awake()
        {
            InitializeIfNeeded();
        }

        void OnDisable()
        {
            StopAllCoroutines();
            KillTween();
        }

        public void Play(Action completion = null)
        {
            if (!gameObject.activeInHierarchy) return;
            
            InitializeIfNeeded();
            StopAllCoroutines();
            KillTween();
            
            _canvasGroup.alpha = 1f;
         
            var children = GetChildren()
                .Select(child => new { Transform = child, CanvasGroup = GetOrAddCanvasGroup(child) })
                .ToList();

            if (children.Count == 0)
            {
                completion?.Invoke();
                return;
            }
            
            int finishedCount = 0;
            int totalCount = children.Count;
            
            for (int i = 0; i < children.Count; i++)
            {
                var child = children[i];
                StartCoroutine(
                    AnimateChild(
                        child.Transform,
                        child.CanvasGroup,
                        i,
                        () =>
                        {
                            finishedCount++;
                            if (finishedCount == totalCount)
                            {
                                KillTween();
                                completion?.Invoke();
                            }
                        }
                    )
                );
            }
        }
        
        void InitializeIfNeeded()
        {
            if (_isInitialized) return;
            _isInitialized = true;
            
            _canvasGroup = GetComponent<CanvasGroup>();
            _canvasGroup.alpha = 0f;
            
            ApplySettings();
        }
        
        void KillTween()
        {
            foreach (var tween in _tweenList)
            {
                tween.Kill(true);
            }
            _tweenList.Clear();
        }

        List<Transform> GetChildren()
        {
            IEnumerable<Transform> children = null;
                        
            // UICollectionViewのGameObjectにアタッチされている場合
            var collectionView = GetComponent<UICollectionView>();
            if (collectionView != null)
            {
                children = collectionView.ScrollRect.content
                    .Cast<Transform>()
                    .Where(t => t.gameObject.GetComponent<UICollectionViewCell>() != null);
            }
            
            // ScrollRectのGameObjectにアタッチされている場合
            if (children == null)
            {
                var scrollRect = GetComponent<ScrollRect>();
                if (scrollRect != null)
                {
                    children = scrollRect.content.Cast<Transform>();
                }
            }

            // ScrollRectでもUICollectionViewでもないGameObjectにアタッチされている場合
            if (children == null)
            {
                children = transform.Cast<Transform>();
            }

            return children
                .Where(t => t.gameObject.activeSelf)
                .OrderByDescending(t => (int)t.localPosition.y)
                .ThenBy(t => (int)t.localPosition.x)
                .ToList();
        }

        CanvasGroup GetOrAddCanvasGroup(Transform child)
        {
            var canvasGroup = child.GetComponent<CanvasGroup>();
            if (canvasGroup != null) return canvasGroup;
                
            return child.gameObject.AddComponent<CanvasGroup>();
        }
        
        IEnumerator AnimateChild(Transform child, CanvasGroup canvasGroup, int index, Action onComplete)
        {
            child.localScale = new Vector3(_setting.InitialScale.x, _setting.InitialScale.y, 1f);
            canvasGroup.alpha = 0f;
            
            var delay = index * _setting.Interval;
            yield return new WaitForSeconds(delay);

            if (child == null)
            {
                onComplete?.Invoke();
                yield break;
            }

            var scaleTween = child
                .DOScale(_setting.TargetScale, _setting.ScaleDuration)
                .SetEase(_setting.ScaleCurve)
                .SetLink(child.gameObject);
            
            _tweenList.Add(scaleTween);
            
            var alphaTween = canvasGroup
                .DOFade(1f, _setting.ScaleDuration)
                .SetEase(_setting.AlphaCurve)
                .SetLink(child.gameObject);
            
            _tweenList.Add(alphaTween);

            yield return new WaitForSeconds(_setting.ScaleDuration);

            onComplete?.Invoke();
        }

        public void ApplySettings()
        {
            if (_settingPresetAsset == null) return;
            _setting = _settingPresetAsset.Setting.Clone();
        }

#if UNITY_EDITOR
        public void SaveSettings()
        {
            if (_settingPresetAsset == null) return;
            Undo.RecordObject(_settingPresetAsset, "ChildScaler 設定保存");
            
            _settingPresetAsset.Setting = _setting.Clone();
            EditorUtility.SetDirty(_settingPresetAsset);
        }
#endif
    }
}