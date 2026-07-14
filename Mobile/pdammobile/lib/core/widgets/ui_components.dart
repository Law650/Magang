import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_colors.dart';

/// Komponen Breadcrumb (contoh: PDAM Digital > Gate Valve)
class Breadcrumb extends StatelessWidget {
  final String title;

  const Breadcrumb({super.key, required this.title});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(
          'PDAM Digital',
          style: GoogleFonts.inter(
            fontSize: 14,
            color: AppColors.primary,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(width: 8),
        const Icon(Icons.chevron_right, size: 16, color: AppColors.textHint),
        const SizedBox(width: 8),
        Text(
          title,
          style: GoogleFonts.inter(
            fontSize: 14,
            color: AppColors.textSecondary,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}

/// Kartu Header untuk Form (contoh: "Formulir Gate Valve")
class FormHeaderCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;

  const FormHeaderCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.cardBorder),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.background,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: AppColors.primary, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppColors.textSecondary,
                      ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Input Counter ( - [ 0 ] + )
class CounterInput extends StatefulWidget {
  final double value;
  final ValueChanged<double> onChanged;
  final double step;
  final double min;
  final double max;
  final String hintText;
  final bool isDecimal;

  const CounterInput({
    super.key,
    required this.value,
    required this.onChanged,
    this.step = 1.0,
    this.min = 0.0,
    this.max = 999.0,
    this.hintText = '',
    this.isDecimal = true,
  });

  @override
  State<CounterInput> createState() => _CounterInputState();
}

class _CounterInputState extends State<CounterInput> {
  late TextEditingController _controller;
  final FocusNode _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: _formatValue(widget.value));
    _focusNode.addListener(_onFocusChange);
  }

  @override
  void didUpdateWidget(CounterInput oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.value != widget.value) {
      final newText = _formatValue(widget.value);
      if (_controller.text != newText && !_focusNode.hasFocus) {
        _controller.text = newText;
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.removeListener(_onFocusChange);
    _focusNode.dispose();
    super.dispose();
  }

  void _onFocusChange() {
    if (!_focusNode.hasFocus) {
      // Replace comma with dot if user typed comma
      final textValue = _controller.text.replaceAll(',', '.');
      final val = double.tryParse(textValue) ?? widget.value;
      final clamped = val.clamp(widget.min, widget.max);
      widget.onChanged(clamped);
      _controller.text = _formatValue(clamped);
    }
  }

  String _formatValue(double val) {
    if (!widget.isDecimal) return val.toInt().toString();
    return val == val.toInt() ? val.toInt().toString() : val.toString();
  }

  void _decrement() {
    _focusNode.unfocus();
    final newVal = double.parse((widget.value - widget.step).toStringAsFixed(4));
    if (newVal >= widget.min) {
      widget.onChanged(newVal);
    }
  }

  void _increment() {
    _focusNode.unfocus();
    final newVal = double.parse((widget.value + widget.step).toStringAsFixed(4));
    if (newVal <= widget.max) {
      widget.onChanged(newVal);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        _buildButton(Icons.remove, _decrement),
        const SizedBox(width: 12),
        Expanded(
          child: Container(
            height: 56,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.cardBorder),
            ),
            alignment: Alignment.center,
            child: TextField(
              controller: _controller,
              focusNode: _focusNode,
              textAlign: TextAlign.center,
              keyboardType: TextInputType.numberWithOptions(decimal: widget.isDecimal),
              style: GoogleFonts.inter(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppColors.textPrimary,
              ),
              decoration: const InputDecoration(
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: EdgeInsets.zero,
                isDense: true,
              ),
              onChanged: (val) {
                String textValue = val.replaceAll(',', '.');
                if (!widget.isDecimal) {
                  textValue = textValue.replaceAll('.', '');
                }

                final parsed = double.tryParse(textValue);
                if (parsed != null) {
                  widget.onChanged(parsed.clamp(widget.min, widget.max));
                }
              },
              onSubmitted: (val) {
                _focusNode.unfocus();
              },
            ),
          ),
        ),
        const SizedBox(width: 12),
        _buildButton(Icons.add, _increment),
      ],
    );
  }

  Widget _buildButton(IconData icon, VoidCallback onTap) {
    return Material(
      color: AppColors.primary,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          width: 56,
          height: 56,
          alignment: Alignment.center,
          child: Icon(icon, color: AppColors.textOnPrimary),
        ),
      ),
    );
  }
}

/// Custom Toggle Button
class CustomToggleButton extends StatelessWidget {
  final String option1Text;
  final IconData option1Icon;
  final Color option1Color;
  
  final String option2Text;
  final IconData option2Icon;
  final Color option2Color;

  final int selectedIndex; // 0 for option1, 1 for option2
  final ValueChanged<int> onChanged;
  final bool disableOption1;
  final bool disableOption2;

  const CustomToggleButton({
    super.key,
    required this.option1Text,
    required this.option1Icon,
    required this.option1Color,
    required this.option2Text,
    required this.option2Icon,
    required this.option2Color,
    required this.selectedIndex,
    required this.onChanged,
    this.disableOption1 = false,
    this.disableOption2 = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _buildToggleItem(
            text: option1Text,
            icon: option1Icon,
            color: option1Color,
            isSelected: selectedIndex == 0,
            isDisabled: disableOption1,
            onTap: () => onChanged(0),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildToggleItem(
            text: option2Text,
            icon: option2Icon,
            color: option2Color,
            isSelected: selectedIndex == 1,
            isDisabled: disableOption2,
            onTap: () => onChanged(1),
          ),
        ),
      ],
    );
  }

  Widget _buildToggleItem({
    required String text,
    required IconData icon,
    required Color color,
    required bool isSelected,
    required VoidCallback onTap,
    bool isDisabled = false,
  }) {
    return Material(
      color: isDisabled ? AppColors.surface : (isSelected ? color.withValues(alpha: 0.1) : AppColors.surface),
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: isDisabled ? null : onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          height: 56,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isDisabled ? AppColors.cardBorder : (isSelected ? color : AppColors.cardBorder),
              width: isSelected && !isDisabled ? 1.5 : 1.0,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                color: isDisabled ? AppColors.textHint.withValues(alpha: 0.5) : (isSelected ? color : AppColors.textHint),
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                text,
                style: GoogleFonts.inter(
                  fontSize: 16,
                  fontWeight: isSelected && !isDisabled ? FontWeight.w600 : FontWeight.w500,
                  color: isDisabled ? AppColors.textHint.withValues(alpha: 0.5) : (isSelected ? color : AppColors.textSecondary),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class FractionalCounterInput extends StatefulWidget {
  final double value;
  final ValueChanged<double> onChanged;

  const FractionalCounterInput({
    super.key,
    required this.value,
    required this.onChanged,
  });

  @override
  State<FractionalCounterInput> createState() => _FractionalCounterInputState();
}

class _FractionalCounterInputState extends State<FractionalCounterInput> {
  late int _integerPart;
  late double _fractionPart;
  late TextEditingController _controller;
  final FocusNode _focusNode = FocusNode();

  final List<double> _fractions = [0.0, 0.125, 0.25, 0.375, 0.5, 0.625, 0.75, 0.875];
  final List<String> _fractionLabels = ['0', '1/8', '1/4', '3/8', '1/2', '5/8', '3/4', '7/8'];

  @override
  void initState() {
    super.initState();
    _parseValue(widget.value);
    _controller = TextEditingController(text: _integerPart.toString());
    _focusNode.addListener(_onFocusChange);
  }

  @override
  void didUpdateWidget(covariant FractionalCounterInput oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.value != widget.value) {
      _parseValue(widget.value);
      if (!_focusNode.hasFocus) {
        _controller.text = _integerPart.toString();
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.removeListener(_onFocusChange);
    _focusNode.dispose();
    super.dispose();
  }

  void _onFocusChange() {
    if (!_focusNode.hasFocus) {
      final val = int.tryParse(_controller.text) ?? _integerPart;
      setState(() {
        _integerPart = val < 0 ? 0 : val;
        _controller.text = _integerPart.toString();
      });
      _updateValue();
    }
  }

  void _parseValue(double value) {
    _integerPart = value.floor();
    double remainder = value - _integerPart;

    // Find nearest fraction
    double minDiff = 1.0;
    _fractionPart = 0.0;
    for (double f in _fractions) {
      double diff = (remainder - f).abs();
      if (diff < minDiff) {
        minDiff = diff;
        _fractionPart = f;
      }
    }
  }

  void _updateValue() {
    widget.onChanged((_integerPart + _fractionPart));
  }

  void _incrementInt() {
    _focusNode.unfocus();
    setState(() {
      _integerPart++;
      _controller.text = _integerPart.toString();
    });
    _updateValue();
  }

  void _decrementInt() {
    _focusNode.unfocus();
    if (_integerPart > 0) {
      setState(() {
        _integerPart--;
        _controller.text = _integerPart.toString();
      });
      _updateValue();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        // Integer part controls
        Material(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(12),
          child: InkWell(
            onTap: _decrementInt,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: 48,
              height: 56,
              alignment: Alignment.center,
              child: const Icon(Icons.remove, color: Colors.white),
            ),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Container(
            height: 56,
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.cardBorder),
            ),
            alignment: Alignment.center,
            child: TextField(
              controller: _controller,
              focusNode: _focusNode,
              textAlign: TextAlign.center,
              keyboardType: TextInputType.number,
              style: GoogleFonts.inter(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppColors.textPrimary,
              ),
              decoration: const InputDecoration(
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: EdgeInsets.zero,
                isDense: true,
              ),
              onChanged: (val) {
                final parsed = int.tryParse(val);
                if (parsed != null && parsed >= 0) {
                  _integerPart = parsed;
                  _updateValue();
                }
              },
              onSubmitted: (val) {
                _focusNode.unfocus();
              },
            ),
          ),
        ),
        const SizedBox(width: 8),
        Material(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(12),
          child: InkWell(
            onTap: _incrementInt,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: 48,
              height: 56,
              alignment: Alignment.center,
              child: const Icon(Icons.add, color: Colors.white),
            ),
          ),
        ),
        const SizedBox(width: 16),
        // Fraction dropdown
        Expanded(
          child: Container(
            height: 56,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.cardBorder),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<double>(
                value: _fractionPart,
                isExpanded: true,
                dropdownColor: AppColors.surface,
                icon: const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                items: List.generate(_fractions.length, (index) {
                  return DropdownMenuItem(
                    value: _fractions[index],
                    child: Text(
                      _fractionLabels[index],
                      style: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  );
                }),
                onChanged: (val) {
                  if (val != null) {
                    setState(() {
                      _fractionPart = val;
                    });
                    _updateValue();
                  }
                },
              ),
            ),
          ),
        ),
      ],
    );
  }
}
