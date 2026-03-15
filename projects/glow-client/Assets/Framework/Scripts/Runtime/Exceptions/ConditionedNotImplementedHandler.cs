using System;
using WonderPlanet.ToastNotifier;
using WPFramework.Modules.Localization.Terms;

namespace WPFramework.Exceptions
{
    public static class ConditionedNotImplementedHandler
    {
        static ILocalizationTermsSource Terms { get; set; }

        public static void SetTerms(ILocalizationTermsSource terms)
        {
            Terms = terms;
        }

        /// <summary>
        /// FirebaseでNotImplementedExceptionがクラッシュ扱いされて、未実装機能にアクセスしまくるとエラー率が跳ね上がるから、
        /// 実際にThrowされるExceptionはOperationCanceledExceptionにしつつ、トースト表示をするようにする。
        /// </summary>
        /// <param name="needToThrow">OperationCanceledExceptionを投げるかどうか判定</param>
        /// <exception cref="OperationCanceledException">OperationCanceledExceptionはFirebaseに拾われないのでFirebaseにクラッシュとして方向が来ない</exception>
        public static void ThrowNotImplementedException(bool needToThrow = false)
        {
            var message = Terms == null ? "未実装の機能です" : Terms.Get("toast_not_implemented");
            Toast.MakeText(message).Show();

            if (needToThrow)
            {
                throw new OperationCanceledException();
            }
        }
    }
}
