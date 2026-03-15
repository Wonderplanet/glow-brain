namespace WPFramework.Exceptions
{
    /// <summary>
    /// NotImplementedExceptionのハンドルクラス
    /// </summary>
    public static class NotImpl
    {
        /// <summary>
        /// NotImplementedExceptionのハンドルメソッド
        /// </summary>
        /// <param name="needToThrow">OperationCanceledExceptionを投げるかどうか判定</param>
        public static void Handle(bool needToThrow = false)
        {
            ConditionedNotImplementedHandler.ThrowNotImplementedException(needToThrow);
        }
    }
}
