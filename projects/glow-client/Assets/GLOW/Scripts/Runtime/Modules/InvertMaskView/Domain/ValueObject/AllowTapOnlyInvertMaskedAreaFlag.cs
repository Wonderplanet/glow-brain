namespace GLOW.Modules.InvertMaskView.Domain.ValueObject
{
    public record AllowTapOnlyInvertMaskedAreaFlag(bool Value)
    {
        public static AllowTapOnlyInvertMaskedAreaFlag True { get; } = new AllowTapOnlyInvertMaskedAreaFlag(true);
        public static AllowTapOnlyInvertMaskedAreaFlag False { get; } = new AllowTapOnlyInvertMaskedAreaFlag(false);

        public static implicit operator bool(AllowTapOnlyInvertMaskedAreaFlag allowTapOnlyInvertMaskedAreaFlag) => allowTapOnlyInvertMaskedAreaFlag.Value;
    }
}
