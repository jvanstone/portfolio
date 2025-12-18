import { __ } from "@wordpress/i18n";
import CF7AppsHelpText from "./CF7AppsHelpText";

const CF7AppsNumberField = ({ label, value, name, description, onChange, className, placeholder, disabled }) => {
    const isLabelInline = (className || '').indexOf('label-inline') !== -1;

    if ( isLabelInline ) {
        return (
            <div className="cf7apps-form-group cf7apps-settings" style={ { display: 'flex', alignItems: 'center', gap: '16px' } }>
                <div style={ { minWidth: '200px' } }><label><b>{label}</b></label></div>
                <div style={ { flex: 1 } }>
                    <input 
                        type="number"
                        value={value}
                        name={name}
                        onChange={onChange}
                        className={`cf7apps-form-input ${className}`}
                        placeholder={placeholder}
                        disabled={disabled}
                    />
                    <CF7AppsHelpText description={description} />
                </div>
            </div>
        );
    }

    return (
        <div className="cf7apps-form-group cf7apps-settings">
            <div>
                <label><b>{label}</b></label>
            </div>
            <div>
                <input 
                    type="number"
                    value={value}
                    name={name}
                    onChange={onChange}
                    className={`cf7apps-form-input ${className}`}
                    placeholder={placeholder}
                    disabled={disabled}
                />
            </div>
            <CF7AppsHelpText description={description} />
        </div>
    );
}

export default CF7AppsNumberField;