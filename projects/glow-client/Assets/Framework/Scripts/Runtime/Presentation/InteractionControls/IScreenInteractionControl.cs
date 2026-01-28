using System;
using WonderPlanet.UniTaskSupporter;

namespace WPFramework.Presentation.InteractionControls
{
    public interface IScreenInteractionControl : IAsyncActivityControl
    {
        IDisposable Lock();

        /// <summary>
        /// 一時的な非表示の解除
        /// </summary>
        void Enable()
        {
        }

        /// <summary>
        /// 一時的に非表示にしたいときの処理を記述。
        /// Screen処理のあとにダイアログを出さなければ行けないケースのときなど。
        /// </summary>
        void Disable()
        {
        }
    }
}
