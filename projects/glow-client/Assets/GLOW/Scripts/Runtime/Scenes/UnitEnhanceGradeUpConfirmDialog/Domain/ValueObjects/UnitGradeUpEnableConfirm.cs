namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.ValueObjects
{
    /// <summary> ユニットのグレードアップ可能か（確認ボタンが押せるか） </summary>
    public record UnitGradeUpEnableConfirm(bool Value)
    {
        public static UnitGradeUpEnableConfirm Enable { get; } = new UnitGradeUpEnableConfirm(true);
        public static UnitGradeUpEnableConfirm Disable { get; } = new UnitGradeUpEnableConfirm(false);

        public static UnitGradeUpEnableConfirm Create(bool isEnable)
        {
            return isEnable ? Enable : Disable;
        }

        public bool IsEnable()
        {
            return ReferenceEquals(this, Enable);
        }
    }
}
