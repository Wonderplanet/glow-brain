using System.Collections.Generic;
using UnityEngine;

namespace WPFramework.Modules.TimeControls
{
    public sealed class TimeManipulator
    {
        readonly float _originalTimeScale;
        readonly Stack<float> _timeScaleStack = new Stack<float>();

        public TimeManipulator()
        {
            // NOTE: Unityのメソッドを実行しているのでUnityが起動している時にインスタンス化すること
            _originalTimeScale = Time.timeScale;
        }

        public void SetTimeScale(float newTimeScale)
        {
            // NOTE: 変更スタックをからにしてから変更する
            _timeScaleStack.Clear();
            Time.timeScale = newTimeScale;
        }

        public void ResetTimeScale()
        {
            // NOTE: 変更スタックをからにしてから変更する
            _timeScaleStack.Clear();
            Time.timeScale = _originalTimeScale;
        }

        public void PushTimeScale(float newTimeScale)
        {
            Time.timeScale = newTimeScale;
            _timeScaleStack.Push(newTimeScale);
        }

        public void PopTimeScale()
        {
            if (_timeScaleStack.Count > 1)
            {
                _timeScaleStack.Pop();
                Time.timeScale = _timeScaleStack.Peek();
            }
            else
            {
                _timeScaleStack.Clear();
                Time.timeScale = _originalTimeScale;
            }
        }

        public bool IsTimeControlled()
        {
            return _timeScaleStack.Count != 0;
        }
    }
}
