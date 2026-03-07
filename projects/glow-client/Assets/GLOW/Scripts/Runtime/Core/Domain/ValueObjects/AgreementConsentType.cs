namespace GLOW.Core.Domain.ValueObjects
{
    public record AgreementConsentType(int Value)
    {
        public static AgreementConsentType Empty { get; } = new(-1); // 該当なし
        public static AgreementConsentType Type0 { get; } = new(0); // 概要
        public static AgreementConsentType Type1 { get; } = new(1); // 分析
        public static AgreementConsentType Type2 { get; } = new(2); // 広告出稿
        public static AgreementConsentType Type3 { get; } = new(3); // カスタマイズ広告

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
