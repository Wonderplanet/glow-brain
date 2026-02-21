using UnityEngine;
using UnityEngine.UI;

#if UNITY_EDITOR
using UnityEditor;
using UnityEditor.SceneManagement;
#endif

namespace GLOW.Core.Presentation.Views.UIAnimator
{
    [ExecuteInEditMode]
    public class UIAnimator : MonoBehaviour
    {
        [Header("Position Animation")]
        [SerializeField] RectTransform _positionTarget;
        [SerializeField] bool _animatePosition = true;
        [SerializeField] Vector2 _startPosition;
        [SerializeField] Vector2 _endPosition;
        [SerializeField] AnimationCurve _positionCurve = AnimationCurve.Linear(0, 0, 1, 1);

        [Header("Rotation Animation")]
        [SerializeField] RectTransform _rotationTarget;
        [SerializeField] bool _animateRotation = false;
        [SerializeField] float _startRotation;
        [SerializeField] float _endRotation;
        [SerializeField] AnimationCurve _rotationCurve = AnimationCurve.Linear(0, 0, 1, 1);

        [Header("Scale Animation")]
        [SerializeField] RectTransform _scaleTarget;
        [SerializeField] bool _animateScale = false;
        [SerializeField] Vector2 _startScale = Vector2.one;
        [SerializeField] Vector2 _endScale = Vector2.one;
        [SerializeField] AnimationCurve _scaleCurve = AnimationCurve.Linear(0, 0, 1, 1);

        [Header("Image Color Animation")]
        [SerializeField] Image _colorTarget;
        [SerializeField] bool _animateColor = false;
        [SerializeField] Color _startColor = Color.white;
        [SerializeField] Color _endColor = Color.white;
        [SerializeField] AnimationCurve _colorCurve = AnimationCurve.Linear(0, 0, 1, 1);

        [Header("CanvasGroup Alpha Animation")]
        [SerializeField] CanvasGroup _canvasGroupTarget;
        [SerializeField] bool _animateAlpha = false;
        [SerializeField] float _startAlpha = 1f;
        [SerializeField] float _endAlpha = 0f;
        [SerializeField] AnimationCurve _alphaCurve = AnimationCurve.Linear(0, 0, 1, 1);

        [Header("Animation Settings")]
        [SerializeField] float _animationTime = 1f;
        [SerializeField] bool _loop = false;
        [SerializeField] bool _pingPong = false;
        [SerializeField] bool _synchronize;
        [SerializeField] bool _isAnimating = false;
        
        float _elapsedTime = 0f;
        bool _reverse = false;

#if UNITY_EDITOR
        bool _editorIsAnimating = false;
        double _lastEditorUpdateTime = 0f;
        float _editorElapsedTime = 0f;
        bool _editorReverse = false;
        Vector2 _editorPrevPosition;
        float _editorPrevRotation;
        Vector2 _editorPrevScale;
        Color _editorPrevColor;
        float _editorPrevAlpha;

        bool IsInPrefabMode()
        {
            return PrefabStageUtility.GetCurrentPrefabStage() != null;
        }
#endif

        void ApplyStartValues()
        {
            if (_positionTarget != null)
            {
                _positionTarget.anchoredPosition = _startPosition;
            }

            if (_rotationTarget != null)
            {
                _rotationTarget.localRotation = Quaternion.Euler(0, 0, _startRotation);
            }

            if (_scaleTarget != null)
            {
                _scaleTarget.localScale = new Vector3(_startScale.x, _startScale.y, 1);
            }

            if (_colorTarget != null)
            {
                _colorTarget.color = _startColor;
            }

            if (_canvasGroupTarget != null)
            {
                _canvasGroupTarget.alpha = _startAlpha;
            }
        }

        void Start()
        {
#if UNITY_EDITOR
            if (!Application.isPlaying) return;
#endif
            ApplyStartValues();
            
            if (Application.isPlaying)
            {
                PlayAnimation();
            }
        }

        void OnEnable()
        {
#if UNITY_EDITOR
            if (!Application.isPlaying) return;
#endif
            ApplyStartValues();
            
            if (Application.isPlaying)
            {
                PlayAnimation();
            }
        }

        void Update()
        {
            if (_isAnimating)
            {
                if (_synchronize && _loop)
                {
                    // 同期再生
                    AnimateSynchronized();
                }
                else
                {
                    // 通常再生
                    Animate();
                }
            }
        }

        public void PlayAnimation()
        {
#if UNITY_EDITOR
            if (!Application.isPlaying)
            {
                // ここで保存→そのあと初期値に
                if (_positionTarget != null)
                {
                    _editorPrevPosition = _positionTarget.anchoredPosition;
                }

                if (_rotationTarget != null)
                {
                    _editorPrevRotation = _rotationTarget.localRotation.eulerAngles.z;
                }

                if (_scaleTarget != null)
                {
                    _editorPrevScale = new Vector2(_scaleTarget.localScale.x, _scaleTarget.localScale.y);
                }

                if (_colorTarget != null)
                {
                    _editorPrevColor = _colorTarget.color;
                }

                if (_canvasGroupTarget != null)
                {
                    _editorPrevAlpha = _canvasGroupTarget.alpha;
                }
                
                ApplyStartValues();
            }
#endif
            _elapsedTime = 0f;
            _reverse = false;
            _isAnimating = true;

#if UNITY_EDITOR
            if (!Application.isPlaying)
            {
                _editorElapsedTime = 0f;
                _editorReverse = false;
                _editorIsAnimating = true;
                _lastEditorUpdateTime = EditorApplication.timeSinceStartup;
            }
#endif
        }

        void Animate()
        {
            _elapsedTime += Time.unscaledDeltaTime;
            float t = Mathf.Clamp01(_elapsedTime / _animationTime);
            if (_reverse)
            {
                t = 1 - t;
            }
            
            ApplyAnimationValues(t);

            if (_elapsedTime >= _animationTime)
            {
                if (_pingPong)
                {
                    if (!_reverse)
                    {
                        _reverse = true; 
                        _elapsedTime = 0f;
                    }
                    else if (_loop)
                    {
                        _reverse = false; 
                        _elapsedTime = 0f;
                    }
                    else
                    {
                        _isAnimating = false;
                    }
                }
                else
                {
                    if (_loop)
                    {
                        _reverse = false; 
                        _elapsedTime = 0f;
                    }
                    else
                    {
                        _isAnimating = false;
                    }
                }
            }
        }

        void AnimateSynchronized()
        {
            float syncTime = Time.unscaledTime % _animationTime;
            float t = syncTime / _animationTime;
            
            if (_pingPong)
            {
                t = (int)(Time.unscaledTime / _animationTime) % 2 == 1 ? 1 - t : t;
            }
            
            ApplyAnimationValues(t);
        }

        void ApplyAnimationValues(float t)
        {
            if (_animatePosition && _positionTarget != null)
            {
                _positionTarget.anchoredPosition = Vector2.LerpUnclamped(
                    _startPosition, 
                    _endPosition, 
                    _positionCurve.Evaluate(t));
            }

            if (_animateRotation && _rotationTarget != null)
            {
                _rotationTarget.localRotation = Quaternion.Euler(
                    0, 
                    0,
                    Mathf.LerpUnclamped(_startRotation, _endRotation, _rotationCurve.Evaluate(t)));
            }

            if (_animateScale && _scaleTarget != null)
            {
                Vector2 s = Vector2.LerpUnclamped(_startScale, _endScale, _scaleCurve.Evaluate(t));
                _scaleTarget.localScale = new Vector3(s.x, s.y, 1);
            }

            if (_animateColor && _colorTarget != null)
            {
                _colorTarget.color = Color.LerpUnclamped(_startColor, _endColor, _colorCurve.Evaluate(t));
            }

            if (_animateAlpha && _canvasGroupTarget != null)
            {
                _canvasGroupTarget.alpha = Mathf.LerpUnclamped(_startAlpha, _endAlpha, _alphaCurve.Evaluate(t));
            }
        }

        public void StopAnimation()
        {
            _isAnimating = false;
#if UNITY_EDITOR
            if (!Application.isPlaying)
            {
                // テスト再生終了時、必ず保存していた値に戻す！
                if (_positionTarget != null)
                {
                    _positionTarget.anchoredPosition = _editorPrevPosition;
                }

                if (_rotationTarget != null)
                {
                    _rotationTarget.localRotation = Quaternion.Euler(0, 0, _editorPrevRotation);
                }

                if (_scaleTarget != null)
                {
                    _scaleTarget.localScale = new Vector3(_editorPrevScale.x, _editorPrevScale.y, 1);
                }

                if (_colorTarget != null)
                {
                    _colorTarget.color = _editorPrevColor;
                }

                if (_canvasGroupTarget != null)
                {
                    _canvasGroupTarget.alpha = _editorPrevAlpha;
                }
                
                _editorIsAnimating = false;
            }
            else
#endif
            {
                ApplyStartValues();
            }
        }

#if UNITY_EDITOR
        public void EditorManualUpdate()
        {
            if (!_editorIsAnimating) return;
            
            double now = EditorApplication.timeSinceStartup;
            float delta = (float)(now - _lastEditorUpdateTime);
            
            _lastEditorUpdateTime = now;
            _editorElapsedTime += delta;
            
            float t = Mathf.Clamp01(_editorElapsedTime / _animationTime);
            if (_editorReverse)
            {
                t = 1 - t;
            }
            
            ApplyAnimationValues(t);

            if (_editorElapsedTime >= _animationTime)
            {
                if (_pingPong)
                {
                    if (!_editorReverse)
                    {
                        _editorReverse = true;
                        _editorElapsedTime = 0f;
                    }
                    else
                    {
                        if (_loop)
                        {
                            _editorReverse = false;
                            _editorElapsedTime = 0f;
                        }
                        else
                        {
                            _editorIsAnimating = false;
                        }
                    }
                }
                else
                {
                    if (_loop)
                    {
                        _editorReverse = false;
                        _editorElapsedTime = 0f;
                    }
                    else
                    {
                        _editorIsAnimating = false;
                    }
                }
            }
        }

        public bool IsEditorAnimating()
        {
            return _editorIsAnimating;
        }
#endif
    }
}
